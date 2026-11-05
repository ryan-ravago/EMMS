<?php

namespace Tests\Feature;

use App\Filament\Resources\AssetTagLogs\AssetTagLogLinks;
use App\Filament\Resources\Equipment\Resources\AssetTags\AssetTagResource;
use App\Filament\Resources\RfidTags\Pages\ListRfidTags;
use App\Filament\Resources\RfidTags\Pages\ViewRfidTag;
use App\Filament\Resources\RfidTags\RfidTagResource;
use App\Models\AppUser;
use App\Models\AssetTag;
use App\Models\Equipment;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssetTagGlobalSearchTest extends AssetMovementTestCase
{
    private Equipment $crane;

    private AssetTag $craneTag;

    private AssetTag $otherTag;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'viewer', 'guard_name' => 'web']);

        Filament::setCurrentPanel('admin');

        $this->crane = $this->makeEquipment();
        $this->craneTag = $this->makeTag($this->crane, 'E2806995AAAA1111');
        $this->otherTag = $this->makeTag($this->makeEquipment(), 'E2806995BBBB2222');
    }

    private function signInAs(string $role): AppUser
    {
        $user = $this->makeUser();
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    public function test_a_tag_is_found_by_part_of_its_id_and_links_to_its_own_page(): void
    {
        $this->signInAs('super_admin');

        $results = RfidTagResource::getGlobalSearchResults('aaaa11');

        $this->assertCount(1, $results);
        $result = $results->first();
        $this->assertSame('E2806995AAAA1111', (string) $result->title);
        $this->assertSame(RfidTagResource::getUrl('view', ['record' => $this->craneTag]), $result->url);
        $this->assertSame(trim("{$this->crane->eqm_prc_code} {$this->crane->eqm_name}"), $result->details['Equipment']);
    }

    public function test_a_tag_is_also_found_by_the_code_of_its_equipment(): void
    {
        $this->signInAs('super_admin');

        $results = RfidTagResource::getGlobalSearchResults($this->crane->eqm_prc_code);

        $this->assertSame(['E2806995AAAA1111'], $results->map(fn ($result) => (string) $result->title)->all());
    }

    public function test_tags_appear_once_in_the_search_and_the_nested_resource_is_not_searched(): void
    {
        $this->signInAs('super_admin');

        $this->assertTrue(RfidTagResource::canGloballySearch());
        $this->assertFalse(AssetTagResource::canGloballySearch());
        $this->assertCount(2, RfidTagResource::getGlobalSearchResults('E2806995'));
        $this->assertCount(0, RfidTagResource::getGlobalSearchResults('nothing-like-this'));
    }

    public function test_someone_who_may_not_view_tags_gets_no_results_and_no_list(): void
    {
        $this->signInAs('viewer');

        $this->assertFalse(RfidTagResource::canViewAny());
        $this->assertCount(0, RfidTagResource::getGlobalSearchResults('E2806995'));
    }

    public function test_the_rfid_tags_list_shows_every_tag_with_its_equipment_and_can_be_searched(): void
    {
        $this->signInAs('super_admin');

        Livewire::test(ListRfidTags::class)
            ->assertCanSeeTableRecords([$this->craneTag, $this->otherTag])
            ->assertTableColumnStateSet('asset.eqm_name', $this->crane->eqm_name, $this->craneTag)
            ->searchTable($this->crane->eqm_name)
            ->assertCanSeeTableRecords([$this->craneTag])
            ->assertCanNotSeeTableRecords([$this->otherTag]);
    }

    public function test_a_tag_can_be_opened_from_the_list(): void
    {
        $this->signInAs('super_admin');

        Livewire::test(ViewRfidTag::class, ['record' => $this->craneTag->getKey()])
            ->assertSuccessful()
            ->assertSee('E2806995AAAA1111');
    }

    public function test_the_asset_on_a_tags_page_links_to_the_asset(): void
    {
        $this->signInAs('super_admin');
        $url = AssetTagLogLinks::assetOfTag($this->craneTag->load('asset'));

        $this->assertStringContainsString((string) $this->crane->getKey(), (string) $url);

        Livewire::test(ViewRfidTag::class, ['record' => $this->craneTag->getKey()])
            ->assertSuccessful()
            ->assertSee($this->crane->eqm_name)
            ->assertSee($url, false);
    }

    public function test_a_viewer_who_may_not_open_the_asset_gets_no_link(): void
    {
        $user = $this->signInAs('viewer');
        $user->givePermissionTo(Permission::findOrCreate('View:AssetTagResource', 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertNull(AssetTagLogLinks::assetOfTag($this->craneTag->load('asset')));
    }
}

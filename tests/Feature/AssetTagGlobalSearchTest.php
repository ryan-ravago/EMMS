<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Resources\AssetTags\AssetTagResource;
use App\Models\AppUser;
use App\Models\Equipment;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;

class AssetTagGlobalSearchTest extends AssetMovementTestCase
{
    private Equipment $crane;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'viewer', 'guard_name' => 'web']);

        Filament::setCurrentPanel('admin');

        $this->crane = $this->makeEquipment();
        $this->makeTag($this->crane, 'E2806995AAAA1111');
        $this->makeTag($this->makeEquipment(), 'E2806995BBBB2222');
    }

    private function signInAs(string $role): AppUser
    {
        $user = $this->makeUser();
        $user->assignRole($role);
        $this->actingAs($user);

        return $user;
    }

    public function test_a_tag_is_found_by_part_of_its_id_and_links_to_its_page_under_the_equipment(): void
    {
        $this->signInAs('super_admin');

        $results = AssetTagResource::getGlobalSearchResults('aaaa11');

        $this->assertCount(1, $results);
        $result = $results->first();
        $this->assertSame('E2806995AAAA1111', (string) $result->title);
        $this->assertStringContainsString('/tags/E2806995AAAA1111', $result->url);
        $this->assertStringContainsString((string) $this->crane->eqm_id, $result->url);
        $this->assertSame(trim("{$this->crane->eqm_prc_code} {$this->crane->eqm_name}"), $result->details['Equipment']);
    }

    public function test_the_global_search_lists_the_tag_resource(): void
    {
        $this->signInAs('super_admin');

        $this->assertTrue(AssetTagResource::canGloballySearch());
        $this->assertCount(2, AssetTagResource::getGlobalSearchResults('E2806995'));
        $this->assertCount(0, AssetTagResource::getGlobalSearchResults('nothing-like-this'));
    }

    public function test_someone_who_may_not_view_tags_gets_no_results(): void
    {
        $this->signInAs('viewer');

        $this->assertCount(0, AssetTagResource::getGlobalSearchResults('E2806995'));
    }
}

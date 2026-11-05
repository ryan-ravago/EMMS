<?php

namespace Tests\Feature;

use App\Filament\Resources\AssetTagLogs\AssetTagLogLinks;
use App\Filament\Resources\AssetTagLogs\AssetTagLogResource;
use App\Filament\Resources\AssetTagLogs\Pages\ListAssetTagLogs;
use App\Filament\Resources\AssetTagLogs\Pages\ViewAssetTagLog;
use App\Filament\Resources\RfidTags\RfidTagResource;
use App\Models\AppUser;
use App\Models\AssetMovement;
use App\Models\AssetTagLog;
use App\Models\Location;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssetTagLogResourceTest extends AssetMovementTestCase
{
    private AppUser $admin;

    private AppUser $manager;

    private AppUser $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-05 12:00:00');

        foreach (['super_admin', 'asset_admin', 'asset_manager', 'viewer'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // The migration that grants the permissions is part of what is tested.
        (require base_path('database/migrations/2026_11_05_103310_add_asset_tag_log_view_permissions.php'))->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Filament::setCurrentPanel('admin');

        $this->admin = $this->userWithRole('asset_admin', 'Alma', 'Admin');
        $this->manager = $this->userWithRole('asset_manager', 'Mario', 'Manager');
        $this->viewer = $this->userWithRole('viewer', 'View', 'Er');
    }

    private function userWithRole(string $role, string $first, string $last): AppUser
    {
        $user = $this->makeUser($first, $last)->refresh();
        $user->assignRole($role);

        return $user;
    }

    // ---- the list ------------------------------------------------------------------------------------------

    public function test_the_list_shows_scans_with_their_asset_and_yard_and_can_be_searched_and_filtered(): void
    {
        $crane = $this->makeEquipment();
        $truck = $this->makeEquipment();
        $craneTag = $this->makeTag($crane, 'E2806995AAAA1111');
        $truckTag = $this->makeTag($truck, 'E2806995BBBB2222');
        $out = $this->scan($craneTag, 'OUT', '2026-11-05 08:00:00');
        $in = $this->scan($truckTag, 'IN', '2026-11-04 17:00:00');
        $this->actingAs($this->manager);

        Livewire::test(ListAssetTagLogs::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$out, $in])
            ->assertTableColumnStateSet('tag.asset.eqm_prc_code', $crane->eqm_prc_code, $out)
            ->assertTableColumnStateSet('yard.name', 'Valenzuela Yard', $out)
            // A tag is searched by its start...
            ->searchTable('E2806995AAAA')
            ->assertCanSeeTableRecords([$out])
            ->assertCanNotSeeTableRecords([$in])
            // ...and an asset by its code.
            ->searchTable($truck->eqm_prc_code)
            ->assertCanSeeTableRecords([$in])
            ->assertCanNotSeeTableRecords([$out])
            ->searchTable('')
            ->filterTable('status', 'OUT')
            ->assertCanSeeTableRecords([$out])
            ->assertCanNotSeeTableRecords([$in]);
    }

    public function test_the_list_can_be_filtered_by_yard_and_by_date(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $otherYard = Location::create(['name' => 'Cebu Yard']);
        $here = $this->scan($tag, 'OUT', '2026-11-05 08:00:00');
        $old = new AssetTagLog;
        $old->forceFill(['tag_id' => 'AAA1', 'yard_id' => $otherYard->id, 'status' => 'IN', 'detected_at' => '2026-10-01 09:00:00'])->save();
        $this->actingAs($this->admin);

        Livewire::test(ListAssetTagLogs::class)
            ->loadTable()
            ->filterTable('yard_id', $otherYard->id)
            ->assertCanSeeTableRecords([$old])
            ->assertCanNotSeeTableRecords([$here])
            ->removeTableFilter('yard_id')
            ->filterTable('detected', ['from' => '2026-11-01', 'until' => '2026-11-30'])
            ->assertCanSeeTableRecords([$here])
            ->assertCanNotSeeTableRecords([$old]);
    }

    public function test_the_list_pages_with_ten_rows_by_default_and_can_jump_to_the_last_page(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $logs = collect(range(1, 23))->map(fn (int $second): AssetTagLog => $this->scan($tag, $second % 2 ? 'OUT' : 'IN', sprintf('2026-11-05 08:00:%02d', $second)));
        $oldest = $logs->first();
        $newest = $logs->last();
        $this->actingAs($this->manager);

        $component = Livewire::test(ListAssetTagLogs::class)->loadTable();

        $table = $component->instance()->getTable();
        $this->assertSame([10, 25, 50, 100], $table->getPaginationPageOptions());
        $this->assertSame(10, $table->getDefaultPaginationPageOption());
        $this->assertTrue($table->hasExtremePaginationLinks());

        // 23 scans at 10 a page: 3 pages, and the last holds the 3 oldest.
        $this->assertCount(10, $component->instance()->getTableRecords());
        $this->assertSame(3, $component->instance()->getTableRecords()->lastPage());

        $component->assertCanSeeTableRecords([$newest])->assertCanNotSeeTableRecords([$oldest]);
        $component->call('gotoPage', 3, 'page')
            ->assertCanSeeTableRecords([$oldest])
            ->assertCanNotSeeTableRecords([$newest]);
    }

    public function test_the_tag_and_the_asset_open_their_own_pages(): void
    {
        $equipment = $this->makeEquipment();
        $log = $this->scan($this->makeTag($equipment, 'AAA1'), 'OUT', '2026-11-05 08:00:00');
        $log->load('tag.asset');
        $super = $this->userWithRole('super_admin', 'Super', 'Admin');
        $this->actingAs($super);

        $tagUrl = AssetTagLogLinks::tag($log);
        $assetUrl = AssetTagLogLinks::asset($log);

        $this->assertSame(RfidTagResource::getUrl('view', ['record' => $log->tag]), $tagUrl);
        $this->assertStringContainsString((string) $equipment->getKey(), (string) $assetUrl);

        // They are links in the list and on the scan's own page.
        Livewire::test(ListAssetTagLogs::class)->loadTable()->assertSee($tagUrl, false)->assertSee($assetUrl, false);
        Livewire::test(ViewAssetTagLog::class, ['record' => $log->getKey()])->assertSee($tagUrl, false)->assertSee($assetUrl, false);
    }

    public function test_no_link_is_given_to_a_page_the_viewer_may_not_open(): void
    {
        $log = $this->scan($this->makeTag($this->makeEquipment(), 'AAA1'), 'OUT', '2026-11-05 08:00:00');
        $log->load('tag.asset');

        // Asset manager may see the logs but has no permission for tags or equipment pages here.
        $this->actingAs($this->manager);
        $this->assertNull(AssetTagLogLinks::tag($log));
        $this->assertNull(AssetTagLogLinks::asset($log));
        Livewire::test(ListAssetTagLogs::class)->loadTable()->assertSee('AAA1');
    }

    public function test_the_list_does_not_run_a_query_per_row(): void
    {
        $equipment = $this->makeEquipment();
        $tag = $this->makeTag($equipment, 'AAA1');
        foreach (range(1, 12) as $second) {
            $this->scan($tag, $second % 2 ? 'OUT' : 'IN', sprintf('2026-11-05 08:00:%02d', $second));
        }
        $this->actingAs($this->manager);

        $component = Livewire::test(ListAssetTagLogs::class)->loadTable();

        // Count only what rendering the table does, not the setup above.
        DB::flushQueryLog();
        DB::enableQueryLog();
        $component->call('$refresh');
        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        // The tags, assets and yards are loaded in bulk, so the count does not grow with the 12 rows.
        $this->assertLessThan(12, $queries->filter(fn (string $sql): bool => str_contains($sql, 'asset_tags') || str_contains($sql, 'equipment_units') || str_contains($sql, '"locations"'))->count());
    }

    // ---- one scan ------------------------------------------------------------------------------------------

    public function test_a_scan_shows_the_trip_it_became_and_has_previous_and_next_buttons(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $oldest = $this->scan($tag, 'OUT', '2026-11-05 08:00:00');
        $middle = $this->scan($tag, 'IN', '2026-11-05 09:00:00');
        $newest = $this->scan($tag, 'OUT', '2026-11-05 10:00:00');
        $trip = AssetMovement::create([
            'asset_id' => $tag->asset_parent_id, 'tag_id' => 'AAA1',
            'out_log_id' => $oldest->id, 'time_out' => '2026-11-05 08:00:00', 'yard_out_id' => $this->yard->id,
        ]);
        $this->actingAs($this->manager);

        Livewire::test(ViewAssetTagLog::class, ['record' => $oldest->getKey()])
            ->assertOk()
            ->assertSee("Trip #{$trip->getKey()}");

        Livewire::test(ViewAssetTagLog::class, ['record' => $middle->getKey()])
            ->assertSee('Not part of a trip')
            ->assertActionHasUrl('previousRecord', AssetTagLogResource::getUrl('view', ['record' => $newest]))
            ->assertActionHasUrl('nextRecord', AssetTagLogResource::getUrl('view', ['record' => $oldest]));
    }

    // ---- who may look --------------------------------------------------------------------------------------

    public function test_super_admin_asset_admin_and_asset_manager_can_look_by_default_and_others_cannot(): void
    {
        $super = $this->userWithRole('super_admin', 'Super', 'Admin');
        $log = $this->scan($this->makeTag($this->makeEquipment(), 'AAA1'), 'OUT', '2026-11-05 08:00:00');

        foreach ([$super, $this->admin, $this->manager] as $user) {
            $this->actingAs($user);
            $this->assertTrue(AssetTagLogResource::canViewAny(), $user->user_email);
            $this->assertTrue(AssetTagLogResource::canView($log), $user->user_email);
            Livewire::test(ListAssetTagLogs::class)->assertOk();
        }

        $this->actingAs($this->viewer);
        $this->assertFalse(AssetTagLogResource::canViewAny());
        $this->assertFalse(AssetTagLogResource::canView($log));
        Livewire::test(ListAssetTagLogs::class)->assertForbidden();
    }

    public function test_the_permission_is_dynamic_through_shield(): void
    {
        // Taking it away from asset manager...
        Role::findByName('asset_manager')->revokePermissionTo(['ViewAny:AssetTagLogResource', 'View:AssetTagLogResource']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->manager->fresh());
        $this->assertFalse(AssetTagLogResource::canViewAny());

        // ...and giving it to another role.
        Role::findByName('viewer')->givePermissionTo(['ViewAny:AssetTagLogResource', 'View:AssetTagLogResource']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->viewer->fresh());
        $this->assertTrue(AssetTagLogResource::canViewAny());
    }

    public function test_the_resource_is_read_only_whatever_the_permissions(): void
    {
        $log = $this->scan($this->makeTag($this->makeEquipment(), 'AAA1'), 'OUT', '2026-11-05 08:00:00');
        Role::findByName('viewer')->givePermissionTo(Permission::findOrCreate('Delete:AssetTagLogResource', 'web'));
        Role::findByName('viewer')->givePermissionTo(Permission::findOrCreate('Update:AssetTagLogResource', 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([$this->admin, $this->viewer->fresh(), $this->userWithRole('super_admin', 'Super', 'Admin')] as $user) {
            $this->actingAs($user);
            $this->assertFalse(AssetTagLogResource::canCreate());
            $this->assertFalse(AssetTagLogResource::canEdit($log));
            $this->assertFalse(AssetTagLogResource::canDelete($log));
            $this->assertFalse(AssetTagLogResource::canDeleteAny());
        }

        $this->assertArrayNotHasKey('create', AssetTagLogResource::getPages());
        $this->assertArrayNotHasKey('edit', AssetTagLogResource::getPages());
    }

    public function test_the_migration_adds_the_view_permissions_for_the_three_roles_only(): void
    {
        foreach (['super_admin', 'asset_admin', 'asset_manager'] as $role) {
            $this->assertTrue(Role::findByName($role)->hasPermissionTo('ViewAny:AssetTagLogResource'), $role);
            $this->assertTrue(Role::findByName($role)->hasPermissionTo('View:AssetTagLogResource'), $role);
        }

        $this->assertFalse(Role::findByName('viewer')->hasPermissionTo('ViewAny:AssetTagLogResource'));
        $this->assertSame(['View:AssetTagLogResource', 'ViewAny:AssetTagLogResource'], Permission::where('name', 'like', '%AssetTagLogResource')->orderBy('name')->pluck('name')->all());
    }
}

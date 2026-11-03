<?php

namespace Tests\Feature;

use App\Filament\Resources\RfidRawLogs\Pages\ListRfidRawLogs;
use App\Filament\Resources\RfidRawLogs\Pages\ViewRfidRawLog;
use App\Filament\Resources\RfidRawLogs\RfidRawLogResource;
use App\Models\AppUser;
use App\Models\RfidRawLog;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RfidRawLogPageTest extends AssetMovementTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'viewer', 'guard_name' => 'web']);

        Filament::setCurrentPanel('admin');
    }

    private function signInAs(string $role, array $permissions = []): AppUser
    {
        $user = $this->makeUser();
        $user->assignRole($role);

        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $this->actingAs($user);

        return $user;
    }

    private function raw(string $tag, string $status = 'OUT', string $outcome = RfidRawLog::PENDING): RfidRawLog
    {
        $id = DB::table('rfid_raw_logs')->insertGetId([
            'batch_uuid' => 'batch-1',
            'yard_id' => $this->yard->id,
            'tag_id' => $tag,
            'status' => $status,
            'detected_at' => '2026-11-03 08:00:00',
            'outcome' => $outcome,
        ]);

        return RfidRawLog::findOrFail($id);
    }

    public function test_the_list_shows_every_read_including_unknown_tags(): void
    {
        $known = $this->raw('AAA1', 'OUT', RfidRawLog::PROCESSED);
        $unknown = $this->raw('ZZZ9', 'IN', RfidRawLog::UNKNOWN_TAG);
        $this->signInAs('super_admin');

        Livewire::test(ListRfidRawLogs::class)
            ->assertCanSeeTableRecords([$known, $unknown])
            ->assertTableColumnStateSet('outcome', 'unknown_tag', $unknown)
            ->searchTable('ZZZ9')
            ->assertCanSeeTableRecords([$unknown])
            ->assertCanNotSeeTableRecords([$known]);
    }

    public function test_the_list_can_be_filtered_by_outcome(): void
    {
        $processed = $this->raw('AAA1', 'OUT', RfidRawLog::PROCESSED);
        $unknown = $this->raw('ZZZ9', 'IN', RfidRawLog::UNKNOWN_TAG);
        $this->signInAs('super_admin');

        Livewire::test(ListRfidRawLogs::class)
            ->filterTable('outcome', RfidRawLog::UNKNOWN_TAG)
            ->assertCanSeeTableRecords([$unknown])
            ->assertCanNotSeeTableRecords([$processed]);
    }

    public function test_a_read_can_be_opened(): void
    {
        $log = $this->raw('ZZZ9', 'IN', RfidRawLog::UNKNOWN_TAG);
        $this->signInAs('super_admin');

        Livewire::test(ViewRfidRawLog::class, ['record' => $log->getKey()])
            ->assertOk()
            ->assertSee('ZZZ9')
            ->assertSee('Unknown tag');
    }

    public function test_the_page_is_read_only(): void
    {
        $log = $this->raw('AAA1');
        $this->signInAs('super_admin');

        $this->assertFalse(RfidRawLogResource::canCreate());
        $this->assertFalse(RfidRawLogResource::canEdit($log));
        $this->assertFalse(RfidRawLogResource::canDelete($log));
        $this->assertFalse(RfidRawLogResource::canDeleteAny());
        $this->assertArrayNotHasKey('create', RfidRawLogResource::getPages());
        $this->assertArrayNotHasKey('edit', RfidRawLogResource::getPages());
    }

    public function test_a_user_without_the_permission_cannot_see_the_page(): void
    {
        $this->raw('AAA1');
        $this->signInAs('viewer');

        $this->assertFalse(RfidRawLogResource::canViewAny());
        Livewire::test(ListRfidRawLogs::class)->assertForbidden();
    }

    public function test_a_user_with_the_permission_can_see_the_page_but_not_change_anything(): void
    {
        $log = $this->raw('AAA1');
        $this->signInAs('viewer', ['ViewAny:RfidRawLogResource', 'View:RfidRawLogResource', 'Delete:RfidRawLogResource', 'Update:RfidRawLogResource']);

        $this->assertTrue(RfidRawLogResource::canViewAny());
        $this->assertTrue(RfidRawLogResource::canView($log));
        $this->assertFalse(RfidRawLogResource::canEdit($log));
        $this->assertFalse(RfidRawLogResource::canDelete($log));
    }

    public function test_the_migration_adds_the_permissions_and_gives_them_to_the_asset_admin_role(): void
    {
        $role = Role::create(['name' => 'asset_admin', 'guard_name' => 'web']);

        (require base_path('database/migrations/2026_11_03_132507_add_rfid_raw_log_permissions.php'))->up();

        $this->assertTrue(Permission::where('name', 'ViewAny:RfidRawLogResource')->exists());
        $this->assertTrue(Permission::where('name', 'View:RfidRawLogResource')->exists());
        $this->assertTrue($role->fresh()->hasPermissionTo('ViewAny:RfidRawLogResource'));
    }
}

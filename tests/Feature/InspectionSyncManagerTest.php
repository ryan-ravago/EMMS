<?php

namespace Tests\Feature;

use App\Filament\Pages\AimsInspectionSyncManager;
use App\Filament\Pages\InspectionSyncManager;
use App\Models\AimsInspection;
use App\Models\AppSetting;
use App\Models\AppUser;
use App\Models\MaintenanceInspection;
use App\Services\Google\GoogleSheetReader;
use App\Services\Inspections\AppSheetInspectionSync as Sync;
use App\Support\InspectionSyncSettings;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InspectionSyncManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();
        (require base_path('database/migrations/2026_05_04_023726_create_app_settings_table.php'))->up();

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code');
            $table->string('dep_name');
            $table->boolean('is_maintenance')->default(false);
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->string('eqm_name')->nullable();
            $table->string('eqm_prc_code')->nullable();
        });

        Schema::create('sync_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('sheet_name');
            $table->integer('synced_rows')->default(0);
            $table->integer('last_row_processed')->nullable();
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();
        });

        (require base_path('database/migrations/2026_10_19_162850_create_maintenance_and_aims_inspection_tables.php'))->up();
        (require base_path('database/migrations/2026_10_20_084131_add_work_order_no_to_maintenance_inspections_table.php'))->up();

        DB::table('departments')->insert(['dep_code' => 'MECH', 'dep_name' => 'Mechanical', 'is_maintenance' => true]);
        DB::table('equipment_units')->insert(['eqm_id' => 5, 'eqm_name' => '10 Wheeler Truck #1', 'eqm_prc_code' => '10w#1']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'manager', 'technician', 'asset_admin', 'asset_manager'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        Filament::setCurrentPanel('admin');
    }

    private function makeUser(string $role): AppUser
    {
        $user = AppUser::create([
            'user_fname' => 'Test',
            'user_lname' => ucfirst($role),
            'user_email' => fake()->unique()->safeEmail(),
            'user_dep_id' => 1,
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, list<array<string, string|null>>>|null  $sheets
     */
    private function bindSheets(?array $sheets = null): void
    {
        $sheets ??= $this->sheets();

        $this->app->instance(GoogleSheetReader::class, new class($sheets) extends GoogleSheetReader
        {
            /**
             * @param  array<string, list<array<string, string|null>>>  $sheets
             */
            public function __construct(private array $sheets) {}

            public function rows(string $sheetName, array $columns): array
            {
                return $this->sheets[$sheetName] ?? [];
            }
        });
    }

    private function bindUnreadableSheets(): void
    {
        $this->app->instance(GoogleSheetReader::class, new class extends GoogleSheetReader
        {
            public function rows(string $sheetName, array $columns): array
            {
                throw new RuntimeException('Google is down');
            }
        });
    }

    /**
     * @return array<string, list<array<string, string|null>>>
     */
    private function sheets(): array
    {
        $row = fn (array $columns, array $values): array => array_merge(array_fill_keys($columns, null), $values);

        return [
            'insps' => [
                $row(Sync::MAINTENANCE_COLUMNS, ['insp_id' => 'a', 'insp_no' => 'IN-ME-1', 'insp_status_id' => 'clrd', 'insp_dep_id' => 'MECH', 'insp_eqm_id' => '5', 'insp_req_at' => '10/2/2026 11:52:40']),
                $row(Sync::MAINTENANCE_COLUMNS, ['insp_id' => 'b', 'insp_no' => 'IN-ME-2', 'insp_status_id' => 'pim']),
            ],
            'insp_items' => [],
            'insp_logs' => [],
            'ainsps' => [
                $row(Sync::AIMS_COLUMNS, ['ainsp_id' => 'x', 'ainsp_no' => 'IN-AI-1', 'ainsp_status_id' => 'cncld', 'ainsp_eqm_id' => '5']),
            ],
            'ainsp_items' => [],
            'ainsp_logs' => [],
            'statuses' => [['status_id' => 'clrd', 'status' => 'Completed'], ['status_id' => 'cncld', 'status' => 'Cancelled']],
            'actions' => [],
            'checklists' => [],
        ];
    }

    public function test_only_super_admins_can_open_the_maintenance_inspection_sync_manager(): void
    {
        foreach (['super_admin' => true, 'asset_admin' => false, 'asset_manager' => false, 'manager' => false, 'technician' => false] as $role => $allowed) {
            $this->actingAs($this->makeUser($role));

            $this->assertSame($allowed, InspectionSyncManager::canAccess(), $role);
        }

        $this->actingAs($this->makeUser('asset_admin'));
        Livewire::test(InspectionSyncManager::class)->assertForbidden();
    }

    public function test_asset_admins_asset_managers_and_super_admins_can_open_the_aims_sync_manager(): void
    {
        foreach (['super_admin' => true, 'asset_admin' => true, 'asset_manager' => true, 'manager' => false, 'technician' => false] as $role => $allowed) {
            $this->actingAs($this->makeUser($role));

            $this->assertSame($allowed, AimsInspectionSyncManager::canAccess(), $role);
        }

        $this->actingAs($this->makeUser('manager'));
        Livewire::test(AimsInspectionSyncManager::class)->assertForbidden();
    }

    public function test_navigation_group_depends_on_the_role(): void
    {
        $this->actingAs($this->makeUser('super_admin'));
        $this->assertSame('Super Admin', AimsInspectionSyncManager::getNavigationGroup());

        $this->actingAs($this->makeUser('asset_admin'));
        $this->assertNull(AimsInspectionSyncManager::getNavigationGroup());
    }

    public function test_sync_now_imports_closed_maintenance_inspections_and_logs_who_ran_it(): void
    {
        $this->bindSheets();
        $user = $this->makeUser('super_admin');
        $this->actingAs($user);

        Livewire::test(InspectionSyncManager::class)
            ->callAction('sync')
            ->assertNotified('Sync Complete');

        $this->assertSame(['IN-ME-1'], MaintenanceInspection::query()->pluck('inspection_no')->all());
        $this->assertSame(0, AimsInspection::query()->count(), 'the maintenance sync must not touch AIMS inspections');

        $log = Activity::query()->where('properties->task', 'Maintenance inspection sync')->sole();
        $this->assertSame('Manual', $log->getExtraProperty('trigger'));
        $this->assertSame('success', $log->getExtraProperty('status'));
        $this->assertSame(1, $log->getExtraProperty('imported'));
        $this->assertSame(1, $log->getExtraProperty('open'));
        $this->assertSame($user->getKey(), $log->causer_id);
        $this->assertStringContainsString('1 imported', $log->description);
    }

    public function test_an_asset_manager_can_sync_aims_inspections_with_the_button(): void
    {
        $this->bindSheets();
        $user = $this->makeUser('asset_manager');
        $this->actingAs($user);

        Livewire::test(AimsInspectionSyncManager::class)
            ->callAction('sync')
            ->assertNotified('Sync Complete');

        $this->assertSame(['IN-AI-1'], AimsInspection::query()->pluck('inspection_no')->all());
        $this->assertSame(0, MaintenanceInspection::query()->count());

        $log = Activity::query()->where('properties->task', 'AIMS inspection sync')->sole();
        $this->assertSame($user->getKey(), $log->causer_id);
        $this->assertSame('Manual', $log->getExtraProperty('trigger'));
    }

    public function test_syncing_again_imports_nothing_new_and_is_logged(): void
    {
        $this->bindSheets();
        $this->actingAs($this->makeUser('super_admin'));

        $page = Livewire::test(InspectionSyncManager::class);
        $page->callAction('sync');
        $page->callAction('sync');

        $this->assertSame(1, MaintenanceInspection::query()->count());

        $logs = Activity::query()->where('properties->task', 'Maintenance inspection sync')->orderBy('id')->get();
        $this->assertSame([1, 0], $logs->map(fn (Activity $log) => $log->getExtraProperty('imported'))->all());
        $this->assertSame([0, 1], $logs->map(fn (Activity $log) => $log->getExtraProperty('skipped'))->all());
    }

    public function test_a_failed_sync_shows_an_error_and_is_logged_as_failed(): void
    {
        $this->bindUnreadableSheets();
        $this->actingAs($this->makeUser('super_admin'));

        Livewire::test(InspectionSyncManager::class)
            ->callAction('sync')
            ->assertNotified('Sync Failed');

        $log = Activity::query()->where('properties->task', 'Maintenance inspection sync')->sole();
        $this->assertSame('failed', $log->getExtraProperty('status'));
        $this->assertStringContainsString('Google is down', (string) $log->getExtraProperty('error'));
        $this->assertSame(0, MaintenanceInspection::query()->count());
    }

    public function test_a_user_without_access_cannot_run_the_sync_at_all(): void
    {
        $this->bindSheets();
        $this->actingAs($this->makeUser('manager'));

        Livewire::test(InspectionSyncManager::class)->assertForbidden();
        Livewire::test(AimsInspectionSyncManager::class)->assertForbidden();

        $this->assertSame(0, MaintenanceInspection::query()->count());
        $this->assertSame(0, AimsInspection::query()->count());
    }

    public function test_the_schedule_time_defaults_and_can_be_changed_with_a_log_entry(): void
    {
        $this->assertSame('01:00', InspectionSyncSettings::time('maintenance'));
        $this->assertSame('01:15', InspectionSyncSettings::time('aims'));

        $this->actingAs($this->makeUser('super_admin'));

        Livewire::test(InspectionSyncManager::class)
            ->assertSet('data.value', '01:00')
            ->fillForm(['value' => '03:30'])
            ->call('saveSchedule')
            ->assertNotified('Schedule Updated');

        $this->assertSame('03:30', InspectionSyncSettings::time('maintenance'));
        $this->assertSame('01:15', InspectionSyncSettings::time('aims'), 'the AIMS time is separate');
        $this->assertSame('03:30', AppSetting::get('maintenance_inspection_sync_time'));

        $change = Activity::query()->where('event', 'schedule_changed')->sole();
        $this->assertSame('Maintenance inspection sync', $change->getExtraProperty('task'));
        $this->assertSame('1:00 AM', $change->getExtraProperty('from'));
        $this->assertSame('3:30 AM', $change->getExtraProperty('to'));
    }

    public function test_saving_the_same_time_again_does_not_log_a_change(): void
    {
        $this->actingAs($this->makeUser('asset_admin'));

        Livewire::test(AimsInspectionSyncManager::class)
            ->fillForm(['value' => '04:45'])
            ->call('saveSchedule');

        // opening the page again shows the saved time; saving it unchanged is not a schedule change
        Livewire::test(AimsInspectionSyncManager::class)
            ->assertSet('data.value', '04:45')
            ->call('saveSchedule');

        $this->assertSame('04:45', InspectionSyncSettings::time('aims'));
        $this->assertSame(1, Activity::query()->where('event', 'schedule_changed')->count());
    }

    public function test_an_unreadable_saved_time_falls_back_to_the_default(): void
    {
        AppSetting::set('maintenance_inspection_sync_time', 'whenever');

        $this->assertSame('01:00', InspectionSyncSettings::time('maintenance'));
    }

    public function test_the_log_table_only_lists_its_own_syncs(): void
    {
        $this->bindSheets();
        $this->actingAs($this->makeUser('super_admin'));

        Livewire::test(InspectionSyncManager::class)->callAction('sync');
        Livewire::test(AimsInspectionSyncManager::class)->callAction('sync');

        $maintenanceLog = Activity::query()->where('properties->task', 'Maintenance inspection sync')->sole();
        $aimsLog = Activity::query()->where('properties->task', 'AIMS inspection sync')->sole();

        Livewire::test(InspectionSyncManager::class)
            ->assertCanSeeTableRecords([$maintenanceLog])
            ->assertCanNotSeeTableRecords([$aimsLog]);

        Livewire::test(AimsInspectionSyncManager::class)
            ->assertCanSeeTableRecords([$aimsLog])
            ->assertCanNotSeeTableRecords([$maintenanceLog]);
    }

    public function test_a_scheduled_run_is_logged_as_scheduled_without_a_person(): void
    {
        $this->bindSheets();

        $this->artisan('inspections:sync', ['--only' => 'maintenance'])->assertSuccessful();

        $log = Activity::query()->where('properties->task', 'Maintenance inspection sync')->sole();
        $this->assertSame('scheduled', $log->event);
        $this->assertSame('Scheduled', $log->getExtraProperty('trigger'));
        $this->assertNull($log->causer_id);
    }

    public function test_a_run_from_the_command_line_can_be_marked_manual(): void
    {
        $this->bindSheets();

        $this->artisan('inspections:sync', ['--only' => 'aims', '--manual' => true])->assertSuccessful();

        $log = Activity::query()->where('properties->task', 'AIMS inspection sync')->sole();
        $this->assertSame('Manual', $log->getExtraProperty('trigger'));
    }

    public function test_a_dry_run_writes_no_log(): void
    {
        $this->bindSheets();

        $this->artisan('inspections:sync', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(0, Activity::query()->count());
    }
}

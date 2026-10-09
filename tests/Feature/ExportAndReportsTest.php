<?php

namespace Tests\Feature;

use App\Filament\Exports\EquipmentExporter;
use App\Filament\Exports\MaintenanceTaskExporter;
use App\Filament\Exports\WorkOrderExporter;
use App\Filament\Pages\AssetReport;
use App\Filament\Pages\MaintenanceReport;
use App\Filament\Pages\WorkOrderReport;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\Equipment\Pages\ListEquipment;
use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Filament\Resources\MaintenanceTasks\Pages\ListMaintenanceTasks;
use App\Filament\Resources\WorkOrders\Pages\ListWorkOrders;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Filament\Support\ExportPreviewAction;
use App\Models\AppUser;
use App\Models\AssetType;
use App\Models\Equipment;
use App\Models\Export as AppExport;
use App\Models\MaintenanceTask;
use App\Models\WorkOrder;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ExportAndReportsTest extends TestCase
{
    private WorkOrder $ownWorkOrder;

    private WorkOrder $otherWorkOrder;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();
        (require base_path('database/migrations/2026_10_09_090038_create_exports_table.php'))->up();

        DB::connection()->getPdo()->sqliteCreateFunction(
            'FIELD',
            fn (mixed $value, mixed ...$list): int => (int) array_search($value, $list) + 1,
        );

        $this->createSchema();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        AssetType::flushIdCache();

        foreach (['super_admin', 'execom', 'manager'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        DB::table('departments')->insert([
            ['dep_id' => 1, 'dep_name' => 'Operations'],
            ['dep_id' => 2, 'dep_name' => 'Logistics'],
        ]);
        DB::table('statuses')->insert([
            ['status_id' => 'pndwor', 'status_title' => 'Pending WO Review', 'status_color' => 'gray'],
            ['status_id' => 'cmp', 'status_title' => 'Completed', 'status_color' => 'success'],
            ['status_id' => 'pnd', 'status_title' => 'Pending', 'status_color' => 'warning'],
        ]);
        DB::table('priorities')->insert([
            ['prio_id' => 1, 'prio_name' => 'Critical'],
            ['prio_id' => 2, 'prio_name' => 'Low'],
        ]);

        $this->ownWorkOrder = WorkOrder::create([
            'wo_no' => 'WO-OWN',
            'wo_dep_id' => 1,
            'wo_prio_id' => 1,
            'wo_status_id' => 'pndwor',
            'wo_created_dt' => now(),
        ]);
        $this->otherWorkOrder = WorkOrder::create([
            'wo_no' => 'WO-OTHER',
            'wo_dep_id' => 2,
            'wo_prio_id' => 2,
            'wo_status_id' => 'cmp',
            'wo_created_dt' => now(),
        ]);

        Filament::setCurrentPanel('admin');
    }

    public function test_work_order_report_only_lists_the_users_department(): void
    {
        $this->actingAs($this->makeUser('manager', ['View:WorkOrderReport']));

        Livewire::test(WorkOrderReport::class)
            ->assertCanSeeTableRecords([$this->ownWorkOrder])
            ->assertCanNotSeeTableRecords([$this->otherWorkOrder])
            ->assertTableColumnHidden('department.dep_name');
    }

    public function test_execom_sees_every_department_in_the_work_order_report(): void
    {
        $this->actingAs($this->makeUser('execom', ['View:WorkOrderReport']));

        Livewire::test(WorkOrderReport::class)
            ->assertCanSeeTableRecords([$this->ownWorkOrder, $this->otherWorkOrder])
            ->assertTableColumnVisible('department.dep_name');
    }

    public function test_work_order_report_summary_follows_the_filters(): void
    {
        $this->actingAs($this->makeUser('execom', ['View:WorkOrderReport']));

        $page = Livewire::test(WorkOrderReport::class);

        $summary = $page->instance()->getSummary();
        $this->assertSame(2, $summary['total']);
        $this->assertSame(1, $summary['groups']['By status']['Pending WO Review']);
        $this->assertSame(1, $summary['groups']['By status']['Completed']);
        $this->assertSame(1, $summary['groups']['By priority']['Critical']);

        $page->filterTable('wo_status_id', 'cmp');

        $summary = $page->instance()->getSummary();
        $this->assertSame(1, $summary['total']);
        $this->assertSame(0, $summary['groups']['By status']['Pending WO Review']);
    }

    public function test_maintenance_report_counts_overdue_tasks(): void
    {
        $this->actingAs($this->makeUser('execom', ['View:MaintenanceReport']));

        $this->makeTask('Overdue task', dueAt: now()->subDay());
        $this->makeTask('Upcoming task', dueAt: now()->addDay());
        $this->makeTask('Closed task', dueAt: now()->subWeek(), closedAt: now()->subDay(), statusId: 'cmp');

        $page = Livewire::test(MaintenanceReport::class);

        $this->assertSame([
            'Overdue' => 1,
            'Open, not yet due' => 1,
            'Closed' => 1,
        ], $page->instance()->getSummary()['groups']['Timeliness']);
        $this->assertSame(3, $page->instance()->getSummary()['total']);

        $page->filterTable('overdue', true)->assertCountTableRecords(1);
    }

    public function test_maintenance_report_is_scoped_to_the_users_department(): void
    {
        $this->actingAs($this->makeUser('manager', ['View:MaintenanceReport']));

        $own = $this->makeTask('Own task', departmentId: 1);
        $other = $this->makeTask('Other task', departmentId: 2);

        Livewire::test(MaintenanceReport::class)
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_asset_report_lists_assets_with_a_summary(): void
    {
        $this->actingAs($this->makeUser('manager', ['View:AssetReport']));

        $active = Equipment::factory()->equipment()->create(['eqm_is_active' => true]);
        $inactive = Equipment::factory()->equipment()->create(['eqm_is_active' => false]);

        $page = Livewire::test(AssetReport::class)
            ->assertCanSeeTableRecords([$active, $inactive]);

        $summary = $page->instance()->getSummary();
        $this->assertSame(2, $summary['total']);
        $this->assertSame(['Active' => 1, 'Inactive' => 1], $summary['groups']['By status']);
        $this->assertSame(2, $summary['groups']['By asset type']['Equipment']);
    }

    public function test_report_pages_require_their_view_permission(): void
    {
        $this->actingAs($this->makeUser('manager', ['View:AssetReport']));

        $this->assertTrue(AssetReport::canAccess());
        $this->assertFalse(WorkOrderReport::canAccess());
        $this->assertFalse(MaintenanceReport::canAccess());
    }

    public function test_preview_lists_only_the_rows_the_user_may_see(): void
    {
        $this->actingAs($this->makeUser('manager', ['View:WorkOrderReport', 'PreviewExport:WorkOrderResource']));

        $page = Livewire::test(WorkOrderReport::class);

        $preview = ExportPreviewAction::make()->exporter(WorkOrderExporter::class)->preview($page->instance());

        $this->assertSame(1, $preview['total']);
        $this->assertSame(1, $preview['shown']);
        $this->assertContains('WO No.', $preview['headers']);
        $this->assertSame('WO-OWN', $preview['rows'][0][array_search('WO No.', $preview['headers'], true)]);
    }

    public function test_preview_modal_describes_the_filtered_rows(): void
    {
        $this->actingAs($this->makeUser('execom', ['View:WorkOrderReport', 'PreviewExport:WorkOrderResource']));

        $page = Livewire::test(WorkOrderReport::class)->mountAction('previewExport');

        $modal = $page->instance()->getMountedAction()->getModalContent()->render();

        $this->assertStringContainsString('Showing the first 2 of 2', $modal);
        $this->assertStringContainsString('WO-OWN', $modal);
        $this->assertStringContainsString('WO-OTHER', $modal);
    }

    public function test_preview_and_export_buttons_follow_their_own_permissions(): void
    {
        $this->actingAs($this->makeUser('manager', ['View:WorkOrderReport', 'PreviewExport:WorkOrderResource']));

        Livewire::test(WorkOrderReport::class)
            ->assertActionVisible('previewExport')
            ->assertActionHidden('export');

        $this->actingAs($this->makeUser('manager', ['View:WorkOrderReport', 'Export:WorkOrderResource']));

        Livewire::test(WorkOrderReport::class)
            ->assertActionHidden('previewExport')
            ->assertActionVisible('export');
    }

    public function test_super_admin_always_sees_preview_and_export(): void
    {
        $this->actingAs($this->makeUser('super_admin', ['View:AssetReport']));

        Livewire::test(AssetReport::class)
            ->assertActionVisible('previewExport')
            ->assertActionVisible('export');
    }

    public function test_resource_lists_get_preview_and_export_actions(): void
    {
        // Maintenance tasks are limited to managers of the preventive maintenance department.
        DB::table('departments')->insert(['dep_id' => 3, 'dep_code' => 'PREV', 'dep_name' => 'Preventive']);

        $this->actingAs($this->makeUser('manager', [
            'ViewAny:WorkOrderResource',
            'PreviewExport:WorkOrderResource',
            'ViewAny:MaintenanceTaskResource',
            'Export:MaintenanceTaskResource',
        ], departmentId: 3));

        Livewire::test(ListWorkOrders::class)
            ->assertActionVisible('previewExport')
            ->assertActionHidden('export');

        Livewire::test(ListMaintenanceTasks::class)
            ->assertActionHidden('previewExport')
            ->assertActionVisible('export');
    }

    public function test_asset_lists_get_preview_and_export_actions(): void
    {
        $this->actingAs($this->makeUser('manager', ['ViewAny:EquipmentResource', 'Export:EquipmentResource']));

        Livewire::test(ListEquipment::class)
            ->assertActionHidden('previewExport')
            ->assertActionVisible('export');
    }

    public function test_export_only_includes_rows_in_the_users_department(): void
    {
        Bus::fake();

        $this->actingAs($this->makeUser('manager', ['View:WorkOrderReport', 'Export:WorkOrderResource']));

        Livewire::test(WorkOrderReport::class)
            ->callAction('export')
            ->assertHasNoFormErrors();

        $export = Export::query()->sole();

        $this->assertSame(WorkOrderExporter::class, $export->exporter);
        $this->assertSame(1, $export->total_rows);
    }

    public function test_export_finishes_in_a_queue_worker_and_notifies_the_user(): void
    {
        (require base_path('database/migrations/0001_01_01_000002_create_jobs_table.php'))->up();
        (require base_path('database/migrations/2026_10_09_090035_create_notifications_table.php'))->up();
        Storage::fake('local');
        config(['queue.default' => 'database']);

        $user = $this->makeUser('manager', ['View:WorkOrderReport', 'Export:WorkOrderResource']);
        $this->actingAs($user);

        Livewire::test(WorkOrderReport::class)->callAction('export');

        // A queue worker has no logged-in user; run the queued jobs the same way.
        Auth::logout();
        $this->artisan('queue:work', ['--stop-when-empty' => true, '--tries' => 1])->assertSuccessful();

        $this->assertSame(0, DB::table('failed_jobs')->count());

        $export = AppExport::query()->sole();

        $this->assertNotNull($export->completed_at);
        $this->assertSame(1, $export->successful_rows);
        $this->assertTrue($export->user->is($user));
        Storage::disk('local')->assertExists($export->getFileDirectory().'/headers.csv');
        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_policies_gate_export_and_preview_permissions(): void
    {
        $viewer = $this->makeUser('manager', ['Export:EquipmentResource', 'PreviewExport:MaintenanceTaskResource']);
        $superAdmin = $this->makeUser('super_admin');

        $this->assertTrue(Gate::forUser($viewer)->allows('export', Equipment::class));
        $this->assertFalse(Gate::forUser($viewer)->allows('previewExport', Equipment::class));
        $this->assertTrue(Gate::forUser($viewer)->allows('previewExport', MaintenanceTask::class));
        $this->assertFalse(Gate::forUser($viewer)->allows('export', MaintenanceTask::class));
        $this->assertFalse(Gate::forUser($viewer)->allows('export', WorkOrder::class));

        foreach ([Equipment::class, MaintenanceTask::class, WorkOrder::class] as $model) {
            $this->assertTrue(Gate::forUser($superAdmin)->allows('export', $model), $model);
            $this->assertTrue(Gate::forUser($superAdmin)->allows('previewExport', $model), $model);
        }
    }

    public function test_shield_lists_the_export_permissions_for_toggling(): void
    {
        $manage = config('filament-shield.resources.manage');

        foreach ([
            EquipmentResource::class,
            WorkOrderResource::class,
            MaintenanceTaskResource::class,
        ] as $resource) {
            $this->assertContains('export', $manage[$resource], $resource);
            $this->assertContains('previewExport', $manage[$resource], $resource);
        }
    }

    public function test_exporters_neutralise_spreadsheet_formulas(): void
    {
        $workOrder = WorkOrder::create(['wo_no' => '=HYPERLINK("http://evil")', 'wo_dep_id' => 1, 'wo_status_id' => 'pndwor']);
        $task = MaintenanceTask::create(['mt_dep_id' => 1, 'mt_task_log' => '@SUM(A1)', 'mt_status_id' => 'pnd']);
        $asset = Equipment::factory()->equipment()->create(['eqm_name' => '+cmd|calc']);

        $this->assertSame("'=HYPERLINK(\"http://evil\")", (new WorkOrderExporter(new Export, ['wo_no' => 'WO No.'], []))($workOrder)[0]);
        $this->assertSame("'@SUM(A1)", (new MaintenanceTaskExporter(new Export, ['mt_task_log' => 'Task'], []))($task)[0]);
        $this->assertSame("'+cmd|calc", (new EquipmentExporter(new Export, ['eqm_name' => 'Name'], []))($asset)[0]);
    }

    private function createSchema(): void
    {
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
            $table->string('dep_code')->nullable();
            $table->string('dep_name')->nullable();
        });

        Schema::create('statuses', function (Blueprint $table): void {
            $table->string('status_id')->primary();
            $table->string('status_title');
            $table->string('status_color')->nullable();
            $table->string('status_icon')->nullable();
        });

        Schema::create('priorities', function (Blueprint $table): void {
            $table->id('prio_id');
            $table->string('prio_name');
        });

        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
        });

        Schema::create('app_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
        });

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
        });

        Schema::create('equipment_types', function (Blueprint $table): void {
            $table->id('eqmt_id');
            $table->string('eqmt_name');
        });

        Schema::create('equipment_brands', function (Blueprint $table): void {
            $table->id('eqmb_id');
            $table->string('eqmb_name');
        });

        Schema::create('equipment_models', function (Blueprint $table): void {
            $table->id('eqmm_id');
            $table->string('eqmm_name');
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('lifecycle_status_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('year_model')->nullable();
            $table->unsignedBigInteger('eqm_eqmm_id')->nullable();
            $table->unsignedBigInteger('eqm_brand_id')->nullable();
            $table->unsignedBigInteger('eqm_eqmt_id')->nullable();
            $table->string('eqm_name');
            $table->string('eqm_prc_code')->nullable()->unique();
            $table->string('eqm_serial_num')->nullable();
            $table->string('eqm_plate_num')->nullable();
            $table->string('eqm_vin')->nullable();
            $table->string('eqm_chassis_no')->nullable();
            $table->string('eqm_engine')->nullable();
            $table->date('eqm_date_purchased')->nullable();
            $table->date('eqm_next_pm_due_at')->nullable();
            $table->boolean('eqm_is_active')->default(true);
        });

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id('wo_id');
            $table->string('wo_no');
            $table->unsignedBigInteger('wo_eqm_id')->nullable();
            $table->unsignedBigInteger('wo_dep_id')->nullable();
            $table->unsignedBigInteger('wo_prio_id')->nullable();
            $table->string('wo_status_id')->nullable();
            $table->text('wo_req_desc')->nullable();
            $table->text('wo_desc')->nullable();
            $table->text('wo_root_cause')->nullable();
            $table->text('wo_corrective_action')->nullable();
            $table->unsignedBigInteger('wo_created_by')->nullable();
            $table->timestamp('wo_created_dt')->nullable();
            $table->timestamp('wo_closed_dt')->nullable();
        });

        Schema::create('work_order_assignments', function (Blueprint $table): void {
            $table->unsignedBigInteger('woa_wo_id');
            $table->unsignedBigInteger('woa_worker_id');
        });

        Schema::create('maintenance_tasks', function (Blueprint $table): void {
            $table->id('mt_id');
            $table->string('mt_batch_id')->nullable();
            $table->unsignedBigInteger('mt_eqm_id')->nullable();
            $table->string('mt_eqm_log')->nullable();
            $table->unsignedBigInteger('mt_dep_id')->nullable();
            $table->unsignedBigInteger('mt_task_id')->nullable();
            $table->string('mt_task_log')->nullable();
            $table->string('mt_status_id')->nullable();
            $table->timestamp('mt_due_dt')->nullable();
            $table->text('mt_remarks')->nullable();
            $table->timestamp('mt_scheduled_dt')->nullable();
            $table->timestamp('mt_closed_dt')->nullable();
            $table->unsignedBigInteger('mt_by')->nullable();
            $table->timestamp('mt_dt')->nullable();
        });
    }

    private function makeTask(
        string $name,
        int $departmentId = 1,
        $dueAt = null,
        $closedAt = null,
        string $statusId = 'pnd',
    ): MaintenanceTask {
        return MaintenanceTask::create([
            'mt_dep_id' => $departmentId,
            'mt_task_log' => $name,
            'mt_eqm_log' => 'Truck',
            'mt_status_id' => $statusId,
            'mt_due_dt' => $dueAt ?? now()->addDay(),
            'mt_closed_dt' => $closedAt,
            'mt_dt' => now(),
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function makeUser(string $role, array $permissions = [], int $departmentId = 1): AppUser
    {
        $user = AppUser::create([
            'user_fname' => fake()->firstName(),
            'user_lname' => fake()->lastName(),
            'user_email' => fake()->unique()->safeEmail(),
            'user_dep_id' => $departmentId,
            'is_active' => true,
        ]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->assignRole($role);
        $user->givePermissionTo($permissions);

        return $user;
    }
}

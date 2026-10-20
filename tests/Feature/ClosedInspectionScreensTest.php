<?php

namespace Tests\Feature;

use App\Filament\Resources\AimsInspections\Pages\ListAimsInspections;
use App\Filament\Resources\AimsInspections\Pages\ViewAimsInspection;
use App\Filament\Resources\AimsInspections\RelationManagers\ItemsRelationManager as AimsItems;
use App\Filament\Resources\Equipment\RelationManagers\AimsInspectionsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\MaintenanceInspectionsRelationManager;
use App\Filament\Resources\MaintenanceInspections\MaintenanceInspectionResource;
use App\Filament\Resources\MaintenanceInspections\Pages\ListMaintenanceInspections;
use App\Filament\Resources\MaintenanceInspections\Pages\ViewMaintenanceInspection;
use App\Filament\Resources\MaintenanceInspections\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\MaintenanceInspections\RelationManagers\LogsRelationManager;
use App\Models\AimsInspection;
use App\Models\AimsInspectionItem;
use App\Models\AppUser;
use App\Models\Equipment;
use App\Models\MaintenanceInspection;
use App\Models\MaintenanceInspectionItem;
use App\Models\MaintenanceInspectionLog;
use Filament\Facades\Filament;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClosedInspectionScreensTest extends TestCase
{
    private const LEGACY_MIGRATION = 'database/migrations/2026_10_19_162852_move_legacy_insps_into_maintenance_inspections.php';

    private int $mechId;

    private int $elecId;

    private int $aimsId;

    private int $prevId;

    private int $itId;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();

        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code');
            $table->string('dep_name');
            $table->boolean('is_maintenance')->default(false);
        });

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('eqm_name')->nullable();
            $table->string('eqm_prc_code')->nullable();
            $table->boolean('eqm_is_active')->default(true);
        });

        (require base_path('database/migrations/2026_10_19_162850_create_maintenance_and_aims_inspection_tables.php'))->up();
        (require base_path('database/migrations/2026_10_20_084131_add_work_order_no_to_maintenance_inspections_table.php'))->up();

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id('wo_id');
            $table->string('wo_no')->nullable()->unique();
            $table->unsignedBigInteger('wo_dep_id')->nullable();
            $table->unsignedBigInteger('wo_created_by')->nullable();
        });

        $this->mechId = DB::table('departments')->insertGetId(['dep_code' => 'MECH', 'dep_name' => 'Mechanical', 'is_maintenance' => true]);
        $this->elecId = DB::table('departments')->insertGetId(['dep_code' => 'ELEC', 'dep_name' => 'Electrical', 'is_maintenance' => true]);
        $this->prevId = DB::table('departments')->insertGetId(['dep_code' => 'PREV', 'dep_name' => 'Preventive', 'is_maintenance' => true]);
        $this->aimsId = DB::table('departments')->insertGetId(['dep_code' => 'AIMS', 'dep_name' => 'Asset Inventory Management', 'is_maintenance' => false]);
        $this->itId = DB::table('departments')->insertGetId(['dep_code' => 'IT', 'dep_name' => 'Information Technology', 'is_maintenance' => false]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['super_admin', 'manager', 'technician', 'asset_admin', 'asset_manager'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        Filament::setCurrentPanel('admin');
    }

    /**
     * @param  list<string>  $permissions
     */
    /**
     * @param  list<string>  $permissions
     * @param  list<string>  $roles
     */
    private function makeUser(?int $departmentId, array $permissions = [], bool $superAdmin = false, array $roles = []): AppUser
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

        $user->givePermissionTo($permissions);

        if ($superAdmin) {
            $roles[] = 'super_admin';
        }

        if ($roles !== []) {
            $user->assignRole($roles);
        }

        return $user;
    }

    private const MAINTENANCE_VIEW = ['ViewAny:MaintenanceInspectionResource', 'View:MaintenanceInspectionResource'];

    private const AIMS_VIEW = ['ViewAny:AimsInspectionResource', 'View:AimsInspectionResource'];

    public function test_a_department_user_only_sees_their_own_departments_maintenance_inspections(): void
    {
        $mech = MaintenanceInspection::factory()->create();
        $elec = MaintenanceInspection::factory()->forDepartment('ELEC', 'Electrical')->create();

        $this->actingAs($this->makeUser($this->mechId, self::MAINTENANCE_VIEW));

        Livewire::test(ListMaintenanceInspections::class)
            ->assertCanSeeTableRecords([$mech])
            ->assertCanNotSeeTableRecords([$elec]);

        $this->actingAs($this->makeUser($this->elecId, self::MAINTENANCE_VIEW));

        Livewire::test(ListMaintenanceInspections::class)
            ->assertCanSeeTableRecords([$elec])
            ->assertCanNotSeeTableRecords([$mech]);
    }

    public function test_a_super_admin_sees_every_departments_maintenance_inspections(): void
    {
        $mech = MaintenanceInspection::factory()->create();
        $elec = MaintenanceInspection::factory()->forDepartment('ELEC', 'Electrical')->create();

        $this->actingAs($this->makeUser($this->aimsId, self::MAINTENANCE_VIEW, superAdmin: true));

        Livewire::test(ListMaintenanceInspections::class)
            ->assertCanSeeTableRecords([$mech, $elec])
            ->assertTableColumnVisible('department_name');
    }

    public function test_a_user_without_a_department_sees_no_maintenance_inspections(): void
    {
        MaintenanceInspection::factory()->create();

        $this->actingAs($this->makeUser(null, self::MAINTENANCE_VIEW));

        $this->assertSame(0, MaintenanceInspectionResource::getEloquentQuery()->count());
    }

    public function test_a_user_without_the_permission_cannot_open_the_list(): void
    {
        $this->actingAs($this->makeUser($this->mechId));

        Livewire::test(ListMaintenanceInspections::class)->assertForbidden();
    }

    public function test_view_page_shows_the_details_and_the_items_and_logs_tabs_list_their_rows(): void
    {
        $inspection = MaintenanceInspection::factory()->cancelled()->create(['equipment_name' => 'Crane One', 'equipment_prc_code' => 'CR-1']);
        $item = MaintenanceInspectionItem::factory()->create(['maintenance_inspection_id' => $inspection->id, 'task' => 'Lights', 'result' => 'Passed']);
        $log = MaintenanceInspectionLog::factory()->create(['maintenance_inspection_id' => $inspection->id, 'action' => 'Requested']);

        $this->actingAs($this->makeUser($this->mechId, self::MAINTENANCE_VIEW));

        Livewire::test(ViewMaintenanceInspection::class, ['record' => $inspection->id])
            ->assertSuccessful()
            ->assertSee($inspection->inspection_no)
            ->assertSee('Cancelled')
            ->assertSee('Wrong entry');

        Livewire::test(ItemsRelationManager::class, ['ownerRecord' => $inspection, 'pageClass' => ViewMaintenanceInspection::class])
            ->assertCanSeeTableRecords([$item]);

        Livewire::test(LogsRelationManager::class, ['ownerRecord' => $inspection, 'pageClass' => ViewMaintenanceInspection::class])
            ->assertCanSeeTableRecords([$log]);
    }

    public function test_the_work_order_number_links_to_the_work_order_when_it_exists_and_the_user_may_view_it(): void
    {
        $workOrderId = DB::table('work_orders')->insertGetId(['wo_no' => 'WO-MECH-261016001', 'wo_dep_id' => $this->mechId]);
        $inspection = MaintenanceInspection::factory()->create(['work_order_no' => 'WO-MECH-261016001']);

        $this->actingAs($this->makeUser($this->mechId, [...self::MAINTENANCE_VIEW, 'View:WorkOrderResource']));

        $this->assertSame($workOrderId, $inspection->workOrder->wo_id);
        $this->assertSame(
            route('filament.admin.resources.work-orders.view', ['record' => $workOrderId]),
            MaintenanceInspectionResource::workOrderUrl($inspection),
        );

        Livewire::test(ListMaintenanceInspections::class)
            ->assertSee('WO-MECH-261016001')
            ->assertTableColumnFormattedStateSet('work_order_no', 'WO-MECH-261016001', $inspection);
        Livewire::test(ViewMaintenanceInspection::class, ['record' => $inspection->id])
            ->assertSee('WO-MECH-261016001');
    }

    public function test_the_work_order_number_is_plain_text_when_the_work_order_is_missing_or_not_viewable(): void
    {
        DB::table('work_orders')->insert(['wo_no' => 'WO-ELEC-1', 'wo_dep_id' => $this->elecId]);
        $missing = MaintenanceInspection::factory()->create(['work_order_no' => 'WO-NOT-IN-THIS-DB']);
        $otherDepartment = MaintenanceInspection::factory()->create(['work_order_no' => 'WO-ELEC-1']);
        $none = MaintenanceInspection::factory()->create(['work_order_no' => null]);

        $this->actingAs($this->makeUser($this->mechId, [...self::MAINTENANCE_VIEW, 'View:WorkOrderResource']));

        $this->assertNull($missing->workOrder);
        $this->assertNull(MaintenanceInspectionResource::workOrderUrl($missing), 'work order is not in the database');
        $this->assertNull(MaintenanceInspectionResource::workOrderUrl($otherDepartment), 'user may not view another department\'s work order');
        $this->assertNull(MaintenanceInspectionResource::workOrderUrl($none));
        $this->assertSame('WO-NOT-IN-THIS-DB', $missing->work_order_no);
    }

    public function test_a_super_admin_can_open_any_work_order_from_an_inspection(): void
    {
        $workOrderId = DB::table('work_orders')->insertGetId(['wo_no' => 'WO-ELEC-1', 'wo_dep_id' => $this->elecId]);
        $inspection = MaintenanceInspection::factory()->create(['work_order_no' => 'WO-ELEC-1']);

        $this->actingAs($this->makeUser($this->mechId, self::MAINTENANCE_VIEW, superAdmin: true));

        $this->assertStringEndsWith("/work-orders/{$workOrderId}", MaintenanceInspectionResource::workOrderUrl($inspection));
    }

    public function test_deleting_the_work_order_keeps_the_number_on_the_inspection(): void
    {
        DB::table('work_orders')->insert(['wo_no' => 'WO-MECH-1', 'wo_dep_id' => $this->mechId]);
        $inspection = MaintenanceInspection::factory()->create(['work_order_no' => 'WO-MECH-1']);

        DB::table('work_orders')->where('wo_no', 'WO-MECH-1')->delete();

        $this->assertNull($inspection->fresh()->workOrder);
        $this->assertSame('WO-MECH-1', $inspection->fresh()->work_order_no);
    }

    public function test_the_work_order_column_migration_can_be_rolled_back(): void
    {
        (require base_path('database/migrations/2026_10_20_084131_add_work_order_no_to_maintenance_inspections_table.php'))->down();

        $this->assertFalse(Schema::hasColumn('maintenance_inspections', 'work_order_no'));
    }

    public function test_another_departments_inspection_cannot_be_opened(): void
    {
        $elec = MaintenanceInspection::factory()->forDepartment('ELEC', 'Electrical')->create();

        $this->actingAs($this->makeUser($this->mechId, self::MAINTENANCE_VIEW));

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(ViewMaintenanceInspection::class, ['record' => $elec->id]);
    }

    public function test_aims_inspections_are_for_asset_admins_asset_managers_and_super_admins(): void
    {
        $inspection = AimsInspection::factory()->create();

        foreach ([['asset_admin'], ['asset_manager'], ['super_admin']] as [$role]) {
            $this->actingAs($this->makeUser($this->itId, roles: [$role]));

            Livewire::test(ListAimsInspections::class)->assertCanSeeTableRecords([$inspection]);
        }
    }

    public function test_nobody_else_can_open_aims_inspections_even_with_the_permission_or_in_the_aims_department(): void
    {
        $inspection = AimsInspection::factory()->create();

        $outsiders = [
            'aims department, no role' => $this->makeUser($this->aimsId, self::AIMS_VIEW),
            'maintenance manager with the permission' => $this->makeUser($this->mechId, self::AIMS_VIEW, roles: ['manager']),
            'technician with the permission' => $this->makeUser($this->mechId, self::AIMS_VIEW, roles: ['technician']),
        ];

        foreach ($outsiders as $label => $user) {
            $this->actingAs($user);

            Livewire::test(ListAimsInspections::class)->assertForbidden();
            $this->assertFalse(Gate::forUser($user)->allows('view', $inspection), $label);
            $this->assertFalse(AimsInspection::isVisibleTo($user), $label);
        }
    }

    public function test_a_maintenance_manager_sees_only_their_own_departments_inspections_without_any_permission(): void
    {
        $mech = MaintenanceInspection::factory()->create();
        $elec = MaintenanceInspection::factory()->forDepartment('ELEC', 'Electrical')->create();
        $prev = MaintenanceInspection::factory()->forDepartment('PREV', 'Preventive')->create();

        foreach ([[$this->mechId, $mech], [$this->elecId, $elec], [$this->prevId, $prev]] as [$departmentId, $own]) {
            $manager = $this->makeUser($departmentId, roles: ['manager']);
            $this->actingAs($manager);

            $others = collect([$mech, $elec, $prev])->reject(fn ($inspection) => $inspection->is($own))->all();

            Livewire::test(ListMaintenanceInspections::class)
                ->assertCanSeeTableRecords([$own])
                ->assertCanNotSeeTableRecords($others);

            Livewire::test(ViewMaintenanceInspection::class, ['record' => $own->id])->assertSuccessful();

            foreach ($others as $other) {
                $this->assertFalse(Gate::forUser($manager)->allows('view', $other));
            }
        }
    }

    public function test_a_manager_outside_the_maintenance_departments_and_other_roles_see_nothing_by_default(): void
    {
        MaintenanceInspection::factory()->create();

        $users = [
            'manager in IT' => $this->makeUser($this->itId, roles: ['manager']),
            'manager in AIMS' => $this->makeUser($this->aimsId, roles: ['manager']),
            'manager without a department' => $this->makeUser(null, roles: ['manager']),
            'technician in MECH' => $this->makeUser($this->mechId, roles: ['technician']),
            'asset admin' => $this->makeUser($this->aimsId, roles: ['asset_admin']),
        ];

        foreach ($users as $label => $user) {
            $this->actingAs($user);

            Livewire::test(ListMaintenanceInspections::class)->assertForbidden();
            $this->assertFalse(Gate::forUser($user)->allows('viewAny', MaintenanceInspection::class), $label);
        }
    }

    public function test_a_manager_of_a_maintenance_department_cannot_see_another_departments_inspection_by_url(): void
    {
        $elec = MaintenanceInspection::factory()->forDepartment('ELEC', 'Electrical')->create();

        $this->actingAs($this->makeUser($this->mechId, roles: ['manager']));

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(ViewMaintenanceInspection::class, ['record' => $elec->id]);
    }

    public function test_aims_view_page_and_items_tab(): void
    {
        $inspection = AimsInspection::factory()->create(['lessee' => 'Samsung', 'jobsite' => 'Laguna']);
        $item = AimsInspectionItem::factory()->create(['aims_inspection_id' => $inspection->id, 'task' => 'Brakes']);

        $this->actingAs($this->makeUser($this->aimsId, roles: ['asset_manager']));

        Livewire::test(ViewAimsInspection::class, ['record' => $inspection->id])
            ->assertSuccessful()
            ->assertSee('Samsung')
            ->assertSee('Laguna');

        Livewire::test(AimsItems::class, ['ownerRecord' => $inspection, 'pageClass' => ViewAimsInspection::class])
            ->assertCanSeeTableRecords([$item]);
    }

    public function test_inspections_are_read_only_whatever_permissions_a_user_has(): void
    {
        $maintenance = MaintenanceInspection::factory()->create();
        $aims = AimsInspection::factory()->create();

        $user = $this->makeUser($this->mechId, [
            ...self::MAINTENANCE_VIEW, ...self::AIMS_VIEW,
            'Create:MaintenanceInspectionResource', 'Update:MaintenanceInspectionResource', 'Delete:MaintenanceInspectionResource', 'DeleteAny:MaintenanceInspectionResource',
            'Create:AimsInspectionResource', 'Update:AimsInspectionResource', 'Delete:AimsInspectionResource', 'DeleteAny:AimsInspectionResource',
        ], roles: ['manager', 'asset_admin']);

        foreach ([$maintenance, $aims] as $inspection) {
            foreach (['create' => $inspection::class, 'update' => $inspection, 'delete' => $inspection, 'deleteAny' => $inspection::class] as $ability => $argument) {
                $this->assertFalse(Gate::forUser($user)->allows($ability, $argument), "{$ability} should be denied");
            }
        }

        $this->assertTrue(Gate::forUser($user)->allows('view', $maintenance));
        $this->assertFalse(MaintenanceInspectionResource::canCreate());
    }

    public function test_equipment_finds_its_inspections_by_prc_code(): void
    {
        DB::table('equipment_units')->insert([
            ['eqm_id' => 1, 'eqm_name' => 'Crane One', 'eqm_prc_code' => 'CR-1'],
            ['eqm_id' => 2, 'eqm_name' => 'No Code', 'eqm_prc_code' => null],
        ]);
        MaintenanceInspection::factory()->count(2)->create(['equipment_prc_code' => 'CR-1']);
        MaintenanceInspection::factory()->create(['equipment_prc_code' => 'OTHER']);
        MaintenanceInspection::factory()->create(['equipment_prc_code' => null]);
        AimsInspection::factory()->create(['equipment_prc_code' => 'CR-1']);

        $crane = Equipment::query()->findOrFail(1);
        $noCode = Equipment::query()->findOrFail(2);

        $this->assertSame(2, $crane->maintenanceInspections()->count());
        $this->assertSame(1, $crane->aimsInspections()->count());
        $this->assertSame(0, $noCode->maintenanceInspections()->count());
        $this->assertSame(0, $noCode->aimsInspections()->count());
        $this->assertSame('Crane One', MaintenanceInspection::query()->where('equipment_prc_code', 'CR-1')->first()->equipment->eqm_name);
    }

    public function test_equipment_tabs_follow_the_same_access_rules(): void
    {
        DB::table('equipment_units')->insert(['eqm_id' => 1, 'eqm_name' => 'Crane One', 'eqm_prc_code' => 'CR-1']);
        MaintenanceInspection::factory()->create(['equipment_prc_code' => 'CR-1']);
        MaintenanceInspection::factory()->forDepartment('ELEC', 'Electrical')->create(['equipment_prc_code' => 'CR-1']);
        AimsInspection::factory()->create(['equipment_prc_code' => 'CR-1']);
        $crane = Equipment::query()->findOrFail(1);

        $this->actingAs($this->makeUser($this->mechId, roles: ['manager']));
        $this->assertSame('1', MaintenanceInspectionsRelationManager::getBadge($crane, 'view'));
        $this->assertSame('0', AimsInspectionsRelationManager::getBadge($crane, 'view'));
        $this->assertFalse(AimsInspectionsRelationManager::canViewForRecord($crane, 'view'));

        $this->actingAs($this->makeUser($this->itId, roles: ['asset_admin']));
        $this->assertSame('1', AimsInspectionsRelationManager::getBadge($crane, 'view'));
        $this->assertTrue(AimsInspectionsRelationManager::canViewForRecord($crane, 'view'));

        $this->actingAs($this->makeUser($this->mechId, self::MAINTENANCE_VIEW, superAdmin: true));
        $this->assertSame('2', MaintenanceInspectionsRelationManager::getBadge($crane, 'view'));
    }

    public function test_each_inspection_sync_is_scheduled_daily_at_its_default_time(): void
    {
        $events = collect(app(Schedule::class)->events());
        $find = fn (string $kind) => $events->first(fn ($event): bool => str_contains((string) $event->command, 'inspections:sync') && str_contains((string) $event->command, $kind));

        $this->assertSame('0 1 * * *', $find('maintenance')?->expression);
        $this->assertSame('15 1 * * *', $find('aims')?->expression);
        $this->assertTrue($find('maintenance')->withoutOverlapping);
        $this->assertTrue($find('aims')->withoutOverlapping);
    }

    private function createLegacyTables(): void
    {
        Schema::create('insps', function (Blueprint $table): void {
            $table->string('insp_id')->primary();
            $table->string('insp_no')->nullable();
            $table->unsignedBigInteger('insp_dep_id')->nullable();
            $table->unsignedBigInteger('insp_eqm_id')->nullable();
            $table->string('checklist_template_name')->nullable();
            $table->longText('insp_remarks')->nullable();
            $table->string('insp_by')->nullable();
            $table->string('insp_submitted_by')->nullable();
            $table->dateTime('insp_submitted_at')->nullable();
        });

        Schema::create('insp_items', function (Blueprint $table): void {
            $table->string('inspi_id')->primary();
            $table->string('inspi_insp_id');
            $table->string('inspi_task')->nullable();
            $table->string('inspi_result')->nullable();
            $table->longText('inspi_remarks')->nullable();
        });
    }

    public function test_legacy_inspections_are_moved_as_text_and_the_move_can_be_repeated(): void
    {
        $this->createLegacyTables();
        DB::table('equipment_units')->insert(['eqm_id' => 5, 'eqm_name' => '10 Wheeler Truck #1', 'eqm_prc_code' => '10w#1']);
        DB::table('insps')->insert([
            ['insp_id' => 'a1', 'insp_no' => 'INS-MECH-1', 'insp_dep_id' => $this->mechId, 'insp_eqm_id' => 5, 'checklist_template_name' => 'Checklist 1', 'insp_remarks' => 'all good', 'insp_by' => 'tech@g.co', 'insp_submitted_by' => 'tech@g.co', 'insp_submitted_at' => '2026-07-12 00:00:57'],
            ['insp_id' => 'a2', 'insp_no' => null, 'insp_dep_id' => null, 'insp_eqm_id' => null, 'checklist_template_name' => null, 'insp_remarks' => null, 'insp_by' => null, 'insp_submitted_by' => null, 'insp_submitted_at' => null],
        ]);
        DB::table('insp_items')->insert([
            ['inspi_id' => 'i1', 'inspi_insp_id' => 'a1', 'inspi_task' => 'Lights', 'inspi_result' => 'Passed', 'inspi_remarks' => null],
            ['inspi_id' => 'i2', 'inspi_insp_id' => 'a1', 'inspi_task' => 'Horn', 'inspi_result' => 'Failed', 'inspi_remarks' => 'weak'],
        ]);

        $migration = require base_path(self::LEGACY_MIGRATION);
        $migration->up();
        $migration->up();

        $this->assertSame(2, MaintenanceInspection::query()->count());

        $moved = MaintenanceInspection::query()->where('inspection_no', 'INS-MECH-1')->sole();
        $this->assertSame('Completed', $moved->status);
        $this->assertSame('MECH', $moved->department_code);
        $this->assertSame('Mechanical', $moved->department_name);
        $this->assertSame('10w#1', $moved->equipment_prc_code);
        $this->assertSame('10 Wheeler Truck #1', $moved->equipment_name);
        $this->assertSame('Checklist 1', $moved->checklist_name);
        $this->assertSame('tech@g.co', $moved->tech_action_by);
        $this->assertSame('all good', $moved->tech_remarks);
        $this->assertSame('2026-07-12 00:00:57', $moved->closed_at->format('Y-m-d H:i:s'));
        $this->assertSame(['Lights', 'Horn'], $moved->items->pluck('task')->all());
        $this->assertSame(['Passed', 'Failed'], $moved->items->pluck('result')->all());

        // a legacy row without an inspection number still gets a unique, readable one
        $this->assertTrue(MaintenanceInspection::query()->where('inspection_no', 'LEGACY-a2')->exists());
    }

    public function test_legacy_move_does_nothing_when_the_old_tables_are_already_gone(): void
    {
        (require base_path(self::LEGACY_MIGRATION))->up();

        $this->assertSame(0, MaintenanceInspection::query()->count());
    }
}

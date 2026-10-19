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

        $this->mechId = DB::table('departments')->insertGetId(['dep_code' => 'MECH', 'dep_name' => 'Mechanical']);
        $this->elecId = DB::table('departments')->insertGetId(['dep_code' => 'ELEC', 'dep_name' => 'Electrical']);
        $this->aimsId = DB::table('departments')->insertGetId(['dep_code' => 'AIMS', 'dep_name' => 'Asset Inventory Management']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        Filament::setCurrentPanel('admin');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function makeUser(?int $departmentId, array $permissions = [], bool $superAdmin = false): AppUser
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
            $user->assignRole('super_admin');
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

    public function test_another_departments_inspection_cannot_be_opened(): void
    {
        $elec = MaintenanceInspection::factory()->forDepartment('ELEC', 'Electrical')->create();

        $this->actingAs($this->makeUser($this->mechId, self::MAINTENANCE_VIEW));

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(ViewMaintenanceInspection::class, ['record' => $elec->id]);
    }

    public function test_aims_inspections_are_only_for_the_aims_department_and_super_admins(): void
    {
        $inspection = AimsInspection::factory()->create();

        $this->actingAs($this->makeUser($this->aimsId, self::AIMS_VIEW));
        Livewire::test(ListAimsInspections::class)->assertCanSeeTableRecords([$inspection]);

        $this->actingAs($this->makeUser($this->mechId, self::AIMS_VIEW));
        Livewire::test(ListAimsInspections::class)->assertForbidden();

        $this->actingAs($this->makeUser($this->mechId, self::AIMS_VIEW, superAdmin: true));
        Livewire::test(ListAimsInspections::class)->assertCanSeeTableRecords([$inspection]);
    }

    public function test_aims_view_page_and_items_tab(): void
    {
        $inspection = AimsInspection::factory()->create(['lessee' => 'Samsung', 'jobsite' => 'Laguna']);
        $item = AimsInspectionItem::factory()->create(['aims_inspection_id' => $inspection->id, 'task' => 'Brakes']);

        $this->actingAs($this->makeUser($this->aimsId, self::AIMS_VIEW));

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

        $user = $this->makeUser($this->aimsId, [
            ...self::MAINTENANCE_VIEW, ...self::AIMS_VIEW,
            'Create:MaintenanceInspectionResource', 'Update:MaintenanceInspectionResource', 'Delete:MaintenanceInspectionResource', 'DeleteAny:MaintenanceInspectionResource',
            'Create:AimsInspectionResource', 'Update:AimsInspectionResource', 'Delete:AimsInspectionResource', 'DeleteAny:AimsInspectionResource',
        ]);

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

        $this->actingAs($this->makeUser($this->mechId, [...self::MAINTENANCE_VIEW, ...self::AIMS_VIEW]));
        $this->assertSame('1', MaintenanceInspectionsRelationManager::getBadge($crane, 'view'));
        $this->assertSame('0', AimsInspectionsRelationManager::getBadge($crane, 'view'));
        $this->assertFalse(AimsInspectionsRelationManager::canViewForRecord($crane, 'view'));

        $this->actingAs($this->makeUser($this->aimsId, self::AIMS_VIEW));
        $this->assertSame('1', AimsInspectionsRelationManager::getBadge($crane, 'view'));
        $this->assertTrue(AimsInspectionsRelationManager::canViewForRecord($crane, 'view'));

        $this->actingAs($this->makeUser($this->mechId, self::MAINTENANCE_VIEW, superAdmin: true));
        $this->assertSame('2', MaintenanceInspectionsRelationManager::getBadge($crane, 'view'));
    }

    public function test_the_inspection_sync_is_scheduled_daily_at_one_in_the_morning(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event): bool => str_contains((string) $event->command, 'inspections:sync'));

        $this->assertNotNull($event, 'inspections:sync is not scheduled');
        $this->assertSame('0 1 * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
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

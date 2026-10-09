<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Equipment\RelationManagers\WorkOrdersRelationManager;
use App\Models\AppUser;
use App\Models\AssetType;
use App\Models\Equipment;
use App\Models\WorkOrder;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EquipmentWorkOrdersExecomTest extends TestCase
{
    private Equipment $equipment;

    private WorkOrder $ownDepartmentWorkOrder;

    private WorkOrder $otherDepartmentWorkOrder;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();

        DB::connection()->getPdo()->sqliteCreateFunction(
            'FIELD',
            fn (mixed $value, mixed ...$list): int => (int) array_search($value, $list) + 1,
        );

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

        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('eqm_eqmm_id')->nullable();
            $table->string('eqm_name');
            $table->string('eqm_prc_code')->nullable()->unique();
            $table->boolean('eqm_is_active')->default(true);
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

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id('wo_id');
            $table->string('wo_no');
            $table->unsignedBigInteger('wo_eqm_id');
            $table->unsignedBigInteger('wo_dep_id')->nullable();
            $table->unsignedBigInteger('wo_prio_id')->nullable();
            $table->string('wo_status_id')->nullable();
            $table->text('wo_desc')->nullable();
            $table->unsignedBigInteger('wo_created_by')->nullable();
            $table->timestamp('wo_created_dt')->nullable();
        });

        Schema::create('work_order_assignments', function (Blueprint $table): void {
            $table->unsignedBigInteger('woa_wo_id');
            $table->unsignedBigInteger('woa_worker_id');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        AssetType::flushIdCache();

        foreach (['super_admin', 'execom', 'manager'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        DB::table('departments')->insert([
            ['dep_id' => 1, 'dep_name' => 'Operations'],
            ['dep_id' => 2, 'dep_name' => 'Logistics'],
        ]);
        DB::table('statuses')->insert(['status_id' => 'pndwor', 'status_title' => 'Pending', 'status_color' => 'gray']);

        $this->equipment = Equipment::factory()->equipment()->create();
        $this->ownDepartmentWorkOrder = $this->makeWorkOrder('WO-001', departmentId: 1);
        $this->otherDepartmentWorkOrder = $this->makeWorkOrder('WO-002', departmentId: 2);

        Filament::setCurrentPanel('admin');
    }

    public function test_execom_sees_work_orders_from_every_department(): void
    {
        $this->actingAs($this->makeUser('execom'));

        $this->renderWorkOrdersRelationManager()
            ->assertCanSeeTableRecords([$this->ownDepartmentWorkOrder, $this->otherDepartmentWorkOrder])
            ->assertTableColumnVisible('department.dep_name');
    }

    public function test_manager_only_sees_work_orders_from_own_department(): void
    {
        $this->actingAs($this->makeUser('manager'));

        $this->renderWorkOrdersRelationManager()
            ->assertCanSeeTableRecords([$this->ownDepartmentWorkOrder])
            ->assertCanNotSeeTableRecords([$this->otherDepartmentWorkOrder])
            ->assertTableColumnHidden('department.dep_name');
    }

    public function test_work_orders_badge_counts_every_department_for_execom(): void
    {
        $this->actingAs($this->makeUser('execom'));

        $this->assertSame('2', WorkOrdersRelationManager::getBadge($this->equipment, ViewEquipment::class));
    }

    public function test_work_orders_badge_counts_own_department_for_manager(): void
    {
        $this->actingAs($this->makeUser('manager'));

        $this->assertSame('1', WorkOrdersRelationManager::getBadge($this->equipment, ViewEquipment::class));
    }

    public function test_execom_can_view_but_not_change_another_department_work_order(): void
    {
        $execom = $this->makeUser('execom', permissions: ['Update:WorkOrderResource', 'Delete:WorkOrderResource']);

        $this->assertTrue(Gate::forUser($execom)->allows('view', $this->otherDepartmentWorkOrder));
        $this->assertFalse(Gate::forUser($execom)->allows('update', $this->otherDepartmentWorkOrder));
        $this->assertFalse(Gate::forUser($execom)->allows('delete', $this->otherDepartmentWorkOrder));
    }

    public function test_manager_cannot_view_another_department_work_order(): void
    {
        $manager = $this->makeUser('manager');

        $this->assertTrue(Gate::forUser($manager)->allows('view', $this->ownDepartmentWorkOrder));
        $this->assertFalse(Gate::forUser($manager)->allows('view', $this->otherDepartmentWorkOrder));
    }

    private function renderWorkOrdersRelationManager(): mixed
    {
        return Livewire::test(WorkOrdersRelationManager::class, [
            'ownerRecord' => $this->equipment,
            'pageClass' => ViewEquipment::class,
        ]);
    }

    private function makeWorkOrder(string $number, int $departmentId): WorkOrder
    {
        return WorkOrder::create([
            'wo_no' => $number,
            'wo_eqm_id' => $this->equipment->eqm_id,
            'wo_dep_id' => $departmentId,
            'wo_status_id' => 'pndwor',
            'wo_created_dt' => now(),
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function makeUser(string $role, array $permissions = []): AppUser
    {
        $user = AppUser::create([
            'user_fname' => fake()->firstName(),
            'user_lname' => fake()->lastName(),
            'user_email' => fake()->unique()->safeEmail(),
            'user_dep_id' => 1,
            'is_active' => true,
        ]);

        $permissions = array_merge(['View:EquipmentResource', 'ViewAny:WorkOrderResource', 'View:WorkOrderResource'], $permissions);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->assignRole($role);
        $user->givePermissionTo($permissions);

        return $user;
    }
}

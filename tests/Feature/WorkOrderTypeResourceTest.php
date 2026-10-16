<?php

namespace Tests\Feature;

use App\Filament\Resources\WorkOrderTypes\Pages\CreateWorkOrderType;
use App\Filament\Resources\WorkOrderTypes\Pages\EditWorkOrderType;
use App\Filament\Resources\WorkOrderTypes\Pages\ListWorkOrderTypes;
use App\Filament\Resources\WorkOrderTypes\Pages\ViewWorkOrderType;
use App\Models\AppUser;
use App\Models\WorkOrder;
use App\Models\WorkOrderType;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WorkOrderTypeResourceTest extends TestCase
{
    private const array FULL_ACCESS = [
        'ViewAny:WorkOrderTypeResource',
        'View:WorkOrderTypeResource',
        'Create:WorkOrderTypeResource',
        'Update:WorkOrderTypeResource',
        'Delete:WorkOrderTypeResource',
        'DeleteAny:WorkOrderTypeResource',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        (require base_path('database/migrations/2026_10_15_155231_create_work_order_types_table.php'))->up();

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id('wo_id');
            $table->string('wo_no')->nullable();
        });

        (require base_path('database/migrations/2026_10_15_155233_add_wo_type_id_to_work_orders_table.php'))->up();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        Filament::setCurrentPanel('admin');
    }

    public function test_user_with_view_any_permission_can_list_types(): void
    {
        $this->actingAs($this->makeUser(permissions: ['ViewAny:WorkOrderTypeResource']));

        $repair = WorkOrderType::create(['code' => 'REP', 'type' => 'Repair']);
        $inspection = WorkOrderType::create(['code' => 'INS', 'type' => 'Inspection']);

        Livewire::test(ListWorkOrderTypes::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$repair, $inspection])
            ->searchTable('Repair')
            ->assertCanSeeTableRecords([$repair])
            ->assertCanNotSeeTableRecords([$inspection]);
    }

    public function test_view_page_shows_the_type_details_and_work_order_count(): void
    {
        $this->actingAs($this->makeUser(permissions: ['ViewAny:WorkOrderTypeResource', 'View:WorkOrderTypeResource']));

        $type = WorkOrderType::create(['code' => 'REP', 'type' => 'Repair']);
        WorkOrder::create(['wo_no' => 'WO-1', 'wo_type_id' => $type->id]);
        WorkOrder::create(['wo_no' => 'WO-2', 'wo_type_id' => $type->id]);

        Livewire::test(ViewWorkOrderType::class, ['record' => $type->id])
            ->assertSuccessful()
            ->assertSee('Details')
            ->assertSee('REP')
            ->assertSee('Repair')
            ->assertSee('Work Orders')
            ->assertSchemaStateSet(['code' => 'REP', 'type' => 'Repair']);
    }

    public function test_user_without_permission_cannot_list_types(): void
    {
        $this->actingAs($this->makeUser());

        Livewire::test(ListWorkOrderTypes::class)->assertForbidden();
    }

    public function test_super_admin_can_create_a_type(): void
    {
        $this->actingAs($this->makeUser(permissions: self::FULL_ACCESS));

        Livewire::test(CreateWorkOrderType::class)
            ->fillForm(['code' => 'INST', 'type' => 'Installation'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('work_order_types', ['code' => 'INST', 'type' => 'Installation']);
    }

    public function test_code_and_type_are_required_and_unique(): void
    {
        $this->actingAs($this->makeUser(permissions: self::FULL_ACCESS));
        WorkOrderType::create(['code' => 'REP', 'type' => 'Repair']);

        Livewire::test(CreateWorkOrderType::class)
            ->fillForm(['code' => null, 'type' => null])
            ->call('create')
            ->assertHasFormErrors(['code' => 'required', 'type' => 'required']);

        Livewire::test(CreateWorkOrderType::class)
            ->fillForm(['code' => 'REP', 'type' => 'Repair'])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique', 'type' => 'unique']);

        $this->assertSame(1, WorkOrderType::count());
    }

    public function test_code_cannot_be_longer_than_twenty_characters(): void
    {
        $this->actingAs($this->makeUser(permissions: self::FULL_ACCESS));

        Livewire::test(CreateWorkOrderType::class)
            ->fillForm(['code' => str_repeat('A', 21), 'type' => 'Long code'])
            ->call('create')
            ->assertHasFormErrors(['code' => 'max']);
    }

    public function test_editing_a_type_keeps_its_own_code_and_type_valid(): void
    {
        $this->actingAs($this->makeUser(permissions: self::FULL_ACCESS));
        $repair = WorkOrderType::create(['code' => 'REP', 'type' => 'Repair']);

        Livewire::test(EditWorkOrderType::class, ['record' => $repair->id])
            ->fillForm(['code' => 'REP', 'type' => 'Repair and Maintenance'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Repair and Maintenance', $repair->fresh()->type);
    }

    public function test_unused_types_can_be_bulk_deleted(): void
    {
        $this->actingAs($this->makeUser(permissions: self::FULL_ACCESS));
        $types = collect([
            WorkOrderType::create(['code' => 'A', 'type' => 'Type A']),
            WorkOrderType::create(['code' => 'B', 'type' => 'Type B']),
        ]);

        Livewire::test(ListWorkOrderTypes::class)
            ->selectTableRecords($types->pluck('id')->all())
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

        $this->assertSame(0, WorkOrderType::count());
    }

    public function test_types_used_by_work_orders_are_not_bulk_deleted(): void
    {
        $this->actingAs($this->makeUser(permissions: self::FULL_ACCESS));
        $used = WorkOrderType::create(['code' => 'REP', 'type' => 'Repair']);
        $unused = WorkOrderType::create(['code' => 'INS', 'type' => 'Inspection']);
        WorkOrder::create(['wo_no' => 'WO-1', 'wo_type_id' => $used->id]);

        Livewire::test(ListWorkOrderTypes::class)
            ->selectTableRecords([$used->id, $unused->id])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified('Cannot delete types that are used by work orders');

        $this->assertSame(2, WorkOrderType::count());
    }

    public function test_delete_action_is_hidden_for_a_type_in_use(): void
    {
        $this->actingAs($this->makeUser(permissions: self::FULL_ACCESS));
        $used = WorkOrderType::create(['code' => 'REP', 'type' => 'Repair']);
        $unused = WorkOrderType::create(['code' => 'INS', 'type' => 'Inspection']);
        WorkOrder::create(['wo_no' => 'WO-1', 'wo_type_id' => $used->id]);

        Livewire::test(EditWorkOrderType::class, ['record' => $used->id])
            ->assertActionHidden(DeleteAction::class);

        Livewire::test(EditWorkOrderType::class, ['record' => $unused->id])
            ->assertActionVisible(DeleteAction::class);
    }

    public function test_work_orders_count_is_shown_per_type(): void
    {
        $this->actingAs($this->makeUser(permissions: self::FULL_ACCESS));
        $type = WorkOrderType::create(['code' => 'REP', 'type' => 'Repair']);
        WorkOrder::create(['wo_no' => 'WO-1', 'wo_type_id' => $type->id]);
        WorkOrder::create(['wo_no' => 'WO-2', 'wo_type_id' => $type->id]);

        Livewire::test(ListWorkOrderTypes::class)
            ->assertTableColumnStateSet('work_orders_count', 2, $type);
    }

    /**
     * @param  list<string>  $roles
     * @param  list<string>  $permissions
     */
    private function makeUser(array $roles = [], array $permissions = []): AppUser
    {
        $user = AppUser::create([
            'user_fname' => fake()->firstName(),
            'user_lname' => fake()->lastName(),
            'user_email' => fake()->unique()->safeEmail(),
            'user_dep_id' => 1,
            'is_active' => true,
        ]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->assignRole($roles);
        $user->givePermissionTo($permissions);

        return $user;
    }
}

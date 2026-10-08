<?php

namespace Tests\Feature;

use App\Filament\Resources\AppUsers\Pages\CreateAppUser;
use App\Models\AppUser;
use App\Models\Location;
use App\Models\WorkOrder;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SecurityAuthorizationTest extends TestCase
{
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

        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code')->nullable();
            $table->string('dep_name')->nullable();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'manager', 'guard_name' => 'web']);
    }

    public function test_work_order_can_be_updated_and_deleted_by_its_own_department(): void
    {
        $manager = $this->makeUser(departmentId: 1, permissions: ['Update:WorkOrderResource', 'Delete:WorkOrderResource']);
        $workOrder = new WorkOrder(['wo_dep_id' => 1]);

        $this->assertTrue(Gate::forUser($manager)->allows('update', $workOrder));
        $this->assertTrue(Gate::forUser($manager)->allows('delete', $workOrder));
    }

    public function test_work_order_cannot_be_changed_by_another_department(): void
    {
        $manager = $this->makeUser(departmentId: 2, permissions: [
            'Update:WorkOrderResource',
            'Delete:WorkOrderResource',
            'Restore:WorkOrderResource',
            'ForceDelete:WorkOrderResource',
            'Replicate:WorkOrderResource',
        ]);
        $workOrder = new WorkOrder(['wo_dep_id' => 1]);

        foreach (['update', 'delete', 'restore', 'forceDelete', 'replicate'] as $ability) {
            $this->assertFalse(Gate::forUser($manager)->allows($ability, $workOrder), $ability);
        }
    }

    public function test_work_order_cannot_be_changed_by_a_user_without_a_department(): void
    {
        $manager = $this->makeUser(departmentId: null, permissions: ['Update:WorkOrderResource']);

        $this->assertFalse(Gate::forUser($manager)->allows('update', new WorkOrder(['wo_dep_id' => null])));
    }

    public function test_super_admin_can_change_any_department_work_order(): void
    {
        $superAdmin = $this->makeUser(departmentId: 9, roles: ['super_admin']);

        $this->assertTrue(Gate::forUser($superAdmin)->allows('update', new WorkOrder(['wo_dep_id' => 1])));
    }

    public function test_user_manager_cannot_edit_or_delete_a_super_admin(): void
    {
        $userManager = $this->makeUser(permissions: ['Update:AppUserResource', 'Delete:AppUserResource']);
        $superAdmin = $this->makeUser(roles: ['super_admin']);
        $regularUser = $this->makeUser();

        $this->assertFalse(Gate::forUser($userManager)->allows('update', $superAdmin));
        $this->assertFalse(Gate::forUser($userManager)->allows('delete', $superAdmin));
        $this->assertTrue(Gate::forUser($userManager)->allows('update', $regularUser));
        $this->assertTrue(Gate::forUser($userManager)->allows('delete', $regularUser));
    }

    public function test_super_admin_can_edit_another_super_admin(): void
    {
        $superAdmin = $this->makeUser(roles: ['super_admin'], permissions: ['Update:AppUserResource']);

        $this->assertTrue(Gate::forUser($superAdmin)->allows('update', $this->makeUser(roles: ['super_admin'])));
    }

    public function test_only_super_admins_can_impersonate(): void
    {
        $this->assertTrue($this->makeUser(roles: ['super_admin'])->canImpersonate());
        $this->assertFalse($this->makeUser(roles: ['manager'])->canImpersonate());
    }

    public function test_super_admins_and_inactive_users_cannot_be_impersonated(): void
    {
        $this->assertTrue($this->makeUser(roles: ['manager'])->canBeImpersonated());
        $this->assertFalse($this->makeUser(roles: ['super_admin'])->canBeImpersonated());
        $this->assertFalse($this->makeUser(isActive: false)->canBeImpersonated());
    }

    public function test_locations_require_permission(): void
    {
        $user = $this->makeUser();
        $location = new Location;

        foreach (['viewAny', 'create'] as $ability) {
            $this->assertFalse(Gate::forUser($user)->allows($ability, Location::class), $ability);
        }

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertFalse(Gate::forUser($user)->allows($ability, $location), $ability);
        }
    }

    public function test_locations_are_allowed_with_permission_or_super_admin(): void
    {
        $editor = $this->makeUser(permissions: ['ViewAny:LocationResource', 'Update:LocationResource']);
        $superAdmin = $this->makeUser(roles: ['super_admin']);

        $this->assertTrue(Gate::forUser($editor)->allows('viewAny', Location::class));
        $this->assertTrue(Gate::forUser($editor)->allows('update', new Location));
        $this->assertFalse(Gate::forUser($editor)->allows('delete', new Location));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('delete', new Location));
    }

    public function test_role_picker_hides_super_admin_from_non_super_admins(): void
    {
        $this->actingAs($this->makeUser(permissions: ['ViewAny:AppUserResource', 'Create:AppUserResource']));

        Livewire::test(CreateAppUser::class)
            ->assertFormFieldExists('roles', fn (CheckboxList $field): bool => array_values($field->getOptions()) === ['Manager']);
    }

    public function test_role_picker_shows_super_admin_to_super_admins(): void
    {
        $this->actingAs($this->makeUser(roles: ['super_admin'], permissions: ['ViewAny:AppUserResource', 'Create:AppUserResource']));

        Livewire::test(CreateAppUser::class)
            ->assertFormFieldExists('roles', fn (CheckboxList $field): bool => in_array('Super Admin', $field->getOptions(), true));
    }

    /**
     * @param  list<string>  $roles
     * @param  list<string>  $permissions
     */
    private function makeUser(?int $departmentId = 1, array $roles = [], array $permissions = [], bool $isActive = true): AppUser
    {
        $user = AppUser::create([
            'user_fname' => fake()->firstName(),
            'user_lname' => fake()->lastName(),
            'user_email' => fake()->unique()->safeEmail(),
            'user_dep_id' => $departmentId,
            'is_active' => $isActive,
        ]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user->assignRole($roles);
        $user->givePermissionTo($permissions);

        return $user;
    }
}

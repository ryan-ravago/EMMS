<?php

namespace Tests\Feature;

use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Pages\ViewRole;
use App\Filament\Resources\Roles\RelationManagers\UsersRelationManager;
use App\Filament\Resources\Roles\RoleResource;
use App\Models\AppUser;
use App\Models\Role;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleUsersRelationManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();

        if (! Schema::hasColumn('roles', 'display_name')) {
            Schema::table('roles', fn (Blueprint $table) => $table->string('display_name')->nullable());
        }

        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code')->nullable();
            $table->string('dep_name')->nullable();
        });

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin' => 'Super Admin', 'technician' => 'Technician', 'manager' => 'Manager'] as $name => $label) {
            Role::create(['name' => $name, 'guard_name' => 'web', 'display_name' => $label]);
        }

        Filament::setCurrentPanel('admin');
    }

    public function test_viewing_a_role_lists_only_its_users(): void
    {
        $this->actingAs($this->makeUser('Admin', ['super_admin'], ['ViewAny:AppUserResource', 'ViewAny:RoleResource', 'View:RoleResource']));
        $role = Role::findByName('technician');
        $technician = $this->makeUser('Ana', ['technician']);
        $manager = $this->makeUser('Ben', ['manager']);

        Livewire::test(UsersRelationManager::class, ['ownerRecord' => $role, 'pageClass' => ViewRole::class])
            ->assertCanSeeTableRecords([$technician])
            ->assertCanNotSeeTableRecords([$manager])
            ->assertCountTableRecords(1);
    }

    public function test_other_roles_column_shows_every_role_except_the_viewed_one(): void
    {
        $this->actingAs($this->makeUser('Admin', ['super_admin'], ['ViewAny:AppUserResource', 'ViewAny:RoleResource', 'View:RoleResource']));
        $role = Role::findByName('technician');
        $onlyTechnician = $this->makeUser('Ana', ['technician']);
        $alsoManager = $this->makeUser('Ben', ['technician', 'manager']);

        Livewire::test(UsersRelationManager::class, ['ownerRecord' => $role, 'pageClass' => ViewRole::class])
            ->assertTableColumnStateSet('other_roles', null, $onlyTechnician)
            ->assertTableColumnStateSet('other_roles', ['Manager'], $alsoManager)
            ->assertTableColumnStateSet('full_name', 'Ben Cruz', $alsoManager);
    }

    public function test_users_tab_shows_on_the_view_page_only_and_counts_the_users(): void
    {
        $this->actingAs($this->makeUser('Admin', ['super_admin'], ['ViewAny:AppUserResource', 'ViewAny:RoleResource', 'View:RoleResource']));
        $role = Role::findByName('technician');
        $this->makeUser('Ana', ['technician']);
        $this->makeUser('Ben', ['technician', 'manager']);

        $this->assertTrue(UsersRelationManager::canViewForRecord($role, ViewRole::class));
        $this->assertFalse(UsersRelationManager::canViewForRecord($role, EditRole::class));
        $this->assertSame('2', UsersRelationManager::getBadge($role, ViewRole::class));

        Livewire::test(ViewRole::class, ['record' => $role->getKey()])->assertOk();
    }

    public function test_roles_list_and_edit_page_lead_to_the_view_page(): void
    {
        $this->actingAs($this->makeUser('Admin', ['super_admin'], ['ViewAny:RoleResource', 'View:RoleResource', 'Update:RoleResource']));
        $role = Role::findByName('technician');

        $list = Livewire::test(ListRoles::class)->assertTableActionVisible('view', $role);

        $this->assertSame(
            RoleResource::getUrl('view', ['record' => $role]),
            $list->instance()->getTable()->getRecordUrl($role),
        );

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->assertActionVisible('view');
    }

    public function test_users_tab_is_hidden_from_people_who_cannot_list_users(): void
    {
        $this->actingAs($this->makeUser('Mia', ['manager'], ['View:RoleResource']));

        $this->assertFalse(UsersRelationManager::canViewForRecord(Role::findByName('technician'), ViewRole::class));
    }

    /**
     * @param  list<string>  $roles
     * @param  list<string>  $permissions
     */
    private function makeUser(string $firstName, array $roles, array $permissions = []): AppUser
    {
        $user = AppUser::create([
            'user_fname' => $firstName,
            'user_lname' => 'Cruz',
            'user_email' => fake()->unique()->safeEmail(),
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

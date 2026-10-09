<?php

namespace Tests\Feature;

use App\Filament\Resources\AppUsers\Pages\ViewAppUser;
use App\Filament\Resources\Roles\Pages\ViewRole;
use App\Filament\Resources\Roles\RelationManagers\UsersRelationManager;
use App\Models\AppUser;
use App\Models\Role;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ImpersonateActionsTest extends TestCase
{
    private const PERMISSIONS = ['ViewAny:AppUserResource', 'View:AppUserResource', 'Update:AppUserResource', 'View:RoleResource'];

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
            $table->string('user_mname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_avatar')->nullable();
            $table->string('user_contact_no')->nullable();
            $table->string('user_fb_profile_link')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin' => 'Super Admin', 'technician' => 'Technician', 'manager' => 'Manager'] as $name => $label) {
            Role::create(['name' => $name, 'guard_name' => 'web', 'display_name' => $label]);
        }

        Filament::setCurrentPanel('admin');
    }

    public function test_super_admin_can_impersonate_from_a_roles_users_tab(): void
    {
        $this->actingAs($this->makeUser('Admin', ['super_admin'], self::PERMISSIONS));
        $role = Role::findByName('technician');
        $active = $this->makeUser('Ana', ['technician']);
        $inactive = $this->makeUser('Ben', ['technician'], isActive: false);

        Livewire::test(UsersRelationManager::class, ['ownerRecord' => $role, 'pageClass' => ViewRole::class])
            ->assertActionVisible(TestAction::make('impersonate')->table($active))
            ->assertActionHidden(TestAction::make('impersonate')->table($inactive));
    }

    public function test_super_admins_are_never_offered_for_impersonation_in_the_users_tab(): void
    {
        $this->actingAs($this->makeUser('Admin', ['super_admin'], self::PERMISSIONS));
        $otherAdmin = $this->makeUser('Zed', ['super_admin']);

        Livewire::test(UsersRelationManager::class, ['ownerRecord' => Role::findByName('super_admin'), 'pageClass' => ViewRole::class])
            ->assertActionHidden(TestAction::make('impersonate')->table($otherAdmin));
    }

    public function test_only_super_admins_see_impersonate_in_the_users_tab(): void
    {
        $this->actingAs($this->makeUser('Mia', ['manager'], self::PERMISSIONS));
        $technician = $this->makeUser('Ana', ['technician']);

        Livewire::test(UsersRelationManager::class, ['ownerRecord' => Role::findByName('technician'), 'pageClass' => ViewRole::class])
            ->assertActionHidden(TestAction::make('impersonate')->table($technician));
    }

    public function test_super_admin_can_impersonate_from_a_users_view_page(): void
    {
        $this->actingAs($this->makeUser('Admin', ['super_admin'], self::PERMISSIONS));
        $technician = $this->makeUser('Ana', ['technician']);

        Livewire::test(ViewAppUser::class, ['record' => $technician->getKey()])
            ->assertActionVisible('impersonate')
            ->callAction('impersonate');

        $this->assertAuthenticatedAs($technician);
    }

    public function test_only_super_admins_see_impersonate_on_a_users_view_page(): void
    {
        $this->actingAs($this->makeUser('Mia', ['manager'], self::PERMISSIONS));
        $technician = $this->makeUser('Ana', ['technician']);

        Livewire::test(ViewAppUser::class, ['record' => $technician->getKey()])
            ->assertActionHidden('impersonate');
    }

    public function test_viewing_a_super_admin_does_not_offer_impersonate(): void
    {
        $this->actingAs($this->makeUser('Admin', ['super_admin'], self::PERMISSIONS));
        $otherAdmin = $this->makeUser('Zed', ['super_admin']);

        Livewire::test(ViewAppUser::class, ['record' => $otherAdmin->getKey()])
            ->assertActionHidden('impersonate');
    }

    /**
     * @param  list<string>  $roles
     * @param  list<string>  $permissions
     */
    private function makeUser(string $firstName, array $roles, array $permissions = [], bool $isActive = true): AppUser
    {
        $user = AppUser::create([
            'user_fname' => $firstName,
            'user_lname' => 'Cruz',
            'user_email' => fake()->unique()->safeEmail(),
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

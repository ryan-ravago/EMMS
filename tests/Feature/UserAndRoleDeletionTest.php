<?php

namespace Tests\Feature;

use App\Filament\Resources\AppUsers\Pages\EditAppUser;
use App\Filament\Resources\AppUsers\Pages\ListAppUsers;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\AppUser;
use App\Models\Role;
use App\Support\UserRelatedRecords;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserAndRoleDeletionTest extends TestCase
{
    private const ADMIN_PERMISSIONS = [
        'ViewAny:AppUserResource',
        'View:AppUserResource',
        'Update:AppUserResource',
        'Delete:AppUserResource',
        'DeleteAny:AppUserResource',
        'ViewAny:RoleResource',
        'View:RoleResource',
        'Update:RoleResource',
        'Delete:RoleResource',
        'DeleteAny:RoleResource',
    ];

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

        // Same delete rules as the real schema: RESTRICT, CASCADE and SET NULL children.
        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id('wo_id');
            $table->foreignId('wo_created_by')->nullable()->constrained('app_users', 'user_id')->restrictOnDelete();
        });

        Schema::create('worker_reports', function (Blueprint $table): void {
            $table->id('wr_id');
            $table->foreignId('wr_worker_id')->constrained('app_users', 'user_id')->cascadeOnDelete();
        });

        Schema::create('inspections', function (Blueprint $table): void {
            $table->id('ins_id');
            $table->foreignId('ins_by')->nullable()->constrained('app_users', 'user_id')->restrictOnDelete();
            $table->foreignId('ins_submitted_by')->nullable()->constrained('app_users', 'user_id')->restrictOnDelete();
        });

        Schema::create('asset_edit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('performed_by')->nullable()->constrained('app_users', 'user_id')->nullOnDelete();
        });

        Schema::create('equipment_task_checklist_templates', function (Blueprint $table): void {
            $table->id('etct_id');
            $table->unsignedBigInteger('etct_created_by')->nullable();
        });

        (require base_path('database/migrations/2026_10_09_090038_create_exports_table.php'))->up();

        UserRelatedRecords::flushReferences();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin' => 'Super Admin', 'technician' => 'Technician', 'manager' => 'Manager'] as $name => $label) {
            Role::create(['name' => $name, 'guard_name' => 'web', 'display_name' => $label]);
        }

        Filament::setCurrentPanel('admin');
    }

    protected function tearDown(): void
    {
        UserRelatedRecords::flushReferences();

        parent::tearDown();
    }

    public function test_related_records_are_read_from_the_database_and_each_row_counts_once(): void
    {
        $user = $this->makeUser('Juan', ['technician']);

        DB::table('work_orders')->insert([['wo_created_by' => $user->user_id], ['wo_created_by' => $user->user_id]]);
        DB::table('inspections')->insert(['ins_by' => $user->user_id, 'ins_submitted_by' => $user->user_id]);
        DB::table('equipment_task_checklist_templates')->insert(['etct_created_by' => $user->user_id]);
        $this->makeExport($user);

        $related = UserRelatedRecords::for($user);

        $this->assertSame([
            'equipment_task_checklist_templates' => 1,
            'inspections' => 1,
            'work_orders' => 2,
        ], collect($related)->sortKeys()->all());
        $this->assertSame(
            '1 equipment task checklist template, 1 inspection and 2 work orders',
            UserRelatedRecords::describe(collect($related)->sortKeys()->all()),
        );
    }

    public function test_user_with_related_records_is_not_deleted(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser('Juan', ['technician']);
        DB::table('worker_reports')->insert(['wr_worker_id' => $user->user_id]);

        Livewire::test(EditAppUser::class, ['record' => $user->getKey()])
            ->callAction('delete')
            ->assertNotified("Juan Cruz can't be deleted");

        $this->assertModelExists($user);
        $this->assertSame(1, DB::table('worker_reports')->count());
    }

    public function test_user_without_related_records_is_deleted(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser('Juan', ['technician']);
        $this->makeExport($user);

        Livewire::test(EditAppUser::class, ['record' => $user->getKey()])
            ->callAction('delete');

        $this->assertModelMissing($user);
    }

    public function test_bulk_user_delete_deletes_nobody_when_one_user_has_related_records(): void
    {
        $this->actingAsAdmin();
        $clean = $this->makeUser('Ana', ['technician']);
        $busy = $this->makeUser('Juan', ['technician']);
        DB::table('asset_edit_logs')->insert(['performed_by' => $busy->user_id]);

        Livewire::test(ListAppUsers::class)
            ->selectTableRecords([$clean->getKey(), $busy->getKey()])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified('No users were deleted');

        $this->assertModelExists($clean);
        $this->assertModelExists($busy);
    }

    public function test_bulk_user_delete_deletes_users_without_related_records(): void
    {
        $this->actingAsAdmin();
        $first = $this->makeUser('Ana', ['technician']);
        $second = $this->makeUser('Ben', ['technician']);

        Livewire::test(ListAppUsers::class)
            ->selectTableRecords([$first->getKey(), $second->getKey()])
            ->callAction(TestAction::make('delete')->table()->bulk());

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
    }

    public function test_role_without_users_is_deleted(): void
    {
        $this->actingAsAdmin();
        $role = Role::findByName('technician');

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->callAction('delete')
            ->assertNotified('Role deleted');

        $this->assertModelMissing($role);
    }

    public function test_role_deletes_users_whose_only_role_it_is_and_keeps_the_others(): void
    {
        $this->actingAsAdmin();
        $role = Role::findByName('technician');
        $onlyTechnician = $this->makeUser('Ana', ['technician']);
        $alsoManager = $this->makeUser('Ben', ['technician', 'manager']);

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->callAction('delete')
            ->assertNotified('Role deleted, along with 1 user who had no other role');

        $this->assertModelMissing($role);
        $this->assertModelMissing($onlyTechnician);
        $this->assertModelExists($alsoManager);
        $this->assertSame(['manager'], $alsoManager->fresh()->getRoleNames()->all());
    }

    public function test_role_is_not_deleted_when_a_user_it_would_delete_has_related_records(): void
    {
        $this->actingAsAdmin();
        $role = Role::findByName('technician');
        $clean = $this->makeUser('Ana', ['technician']);
        $busy = $this->makeUser('Juan', ['technician']);
        DB::table('work_orders')->insert(['wo_created_by' => $busy->user_id]);

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->callAction('delete')
            ->assertNotified("The Technician role can't be deleted");

        $this->assertModelExists($role);
        $this->assertModelExists($clean);
        $this->assertModelExists($busy);
    }

    public function test_users_with_another_role_do_not_block_the_role_delete(): void
    {
        $this->actingAsAdmin();
        $role = Role::findByName('technician');
        $busyManager = $this->makeUser('Juan', ['technician', 'manager']);
        DB::table('work_orders')->insert(['wo_created_by' => $busyManager->user_id]);

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->callAction('delete')
            ->assertNotified('Role deleted');

        $this->assertModelMissing($role);
        $this->assertModelExists($busyManager);
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $this->actingAsAdmin();
        $role = Role::findByName('super_admin');

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->callAction('delete')
            ->assertNotified("The Super Admin role can't be deleted");

        $this->assertModelExists($role);
    }

    public function test_role_that_would_delete_your_own_account_is_refused(): void
    {
        $manager = $this->makeUser('Mia', ['manager'], self::ADMIN_PERMISSIONS);
        $this->actingAs($manager);
        $role = Role::findByName('manager');

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->callAction('delete')
            ->assertNotified("The Manager role can't be deleted");

        $this->assertModelExists($role);
        $this->assertModelExists($manager);
    }

    public function test_bulk_role_delete_removes_users_left_with_no_role_at_all(): void
    {
        $this->actingAsAdmin();
        $technician = Role::findByName('technician');
        $manager = Role::findByName('manager');
        $both = $this->makeUser('Ben', ['technician', 'manager']);

        Livewire::test(ListRoles::class)
            ->selectTableRecords([$technician->getKey(), $manager->getKey()])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified('2 roles deleted, along with 1 user who had no other role');

        $this->assertModelMissing($technician);
        $this->assertModelMissing($manager);
        $this->assertModelMissing($both);
    }

    public function test_bulk_role_delete_is_refused_when_a_user_left_with_no_role_has_related_records(): void
    {
        $this->actingAsAdmin();
        $technician = Role::findByName('technician');
        $manager = Role::findByName('manager');
        $both = $this->makeUser('Ben', ['technician', 'manager']);
        DB::table('inspections')->insert(['ins_by' => $both->user_id]);

        Livewire::test(ListRoles::class)
            ->selectTableRecords([$technician->getKey(), $manager->getKey()])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified('No roles were deleted');

        $this->assertModelExists($technician);
        $this->assertModelExists($manager);
        $this->assertModelExists($both);
    }

    private function actingAsAdmin(): AppUser
    {
        $admin = $this->makeUser('Admin', ['super_admin'], self::ADMIN_PERMISSIONS);
        $this->actingAs($admin);

        return $admin;
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

    private function makeExport(AppUser $user): void
    {
        DB::table('exports')->insert([
            'file_disk' => 'local',
            'exporter' => 'App\\Filament\\Exports\\WorkOrderExporter',
            'total_rows' => 0,
            'user_id' => $user->user_id,
        ]);
    }
}

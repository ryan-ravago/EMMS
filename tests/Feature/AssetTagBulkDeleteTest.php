<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Pages\EditEquipment;
use App\Filament\Resources\Equipment\RelationManagers\TagsRelationManager;
use App\Models\AppUser;
use App\Models\AssetTag;
use App\Models\AssetType;
use App\Models\Equipment;
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

class AssetTagBulkDeleteTest extends TestCase
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

        Schema::create('asset_tags', function (Blueprint $table): void {
            $table->string('tag_id')->primary();
            $table->unsignedBigInteger('asset_parent_id');
        });

        Schema::create('asset_tag_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('tag_id');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        AssetType::flushIdCache();

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        Filament::setCurrentPanel('admin');
    }

    public function test_user_with_update_permission_can_bulk_delete_tags(): void
    {
        $this->actingAs($this->makeUser(permissions: [
            'View:EquipmentResource',
            'ViewAny:AssetTagResource',
            'DeleteAny:AssetTagResource',
            'Update:AssetTagResource',
        ]));

        $equipment = Equipment::factory()->equipment()->create();
        AssetTag::query()->insert([
            ['tag_id' => 'TAG-1', 'asset_parent_id' => $equipment->eqm_id],
            ['tag_id' => 'TAG-2', 'asset_parent_id' => $equipment->eqm_id],
        ]);

        Livewire::test(TagsRelationManager::class, ['ownerRecord' => $equipment, 'pageClass' => EditEquipment::class])
            ->selectTableRecords(['TAG-1', 'TAG-2'])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(0, AssetTag::query()->count());
    }

    public function test_user_without_update_permission_cannot_bulk_delete_tags(): void
    {
        $this->actingAs($this->makeUser(permissions: [
            'View:EquipmentResource',
            'ViewAny:AssetTagResource',
            'DeleteAny:AssetTagResource',
        ]));

        $equipment = Equipment::factory()->equipment()->create();
        AssetTag::query()->insert(['tag_id' => 'TAG-1', 'asset_parent_id' => $equipment->eqm_id]);

        Livewire::test(TagsRelationManager::class, ['ownerRecord' => $equipment, 'pageClass' => EditEquipment::class])
            ->selectTableRecords(['TAG-1'])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

        $this->assertSame(1, AssetTag::query()->count());
    }

    public function test_super_admin_can_bulk_delete_tags(): void
    {
        $this->actingAs($this->makeUser(roles: ['super_admin']));

        $equipment = Equipment::factory()->equipment()->create();
        AssetTag::query()->insert(['tag_id' => 'TAG-1', 'asset_parent_id' => $equipment->eqm_id]);

        Livewire::test(TagsRelationManager::class, ['ownerRecord' => $equipment, 'pageClass' => EditEquipment::class])
            ->selectTableRecords(['TAG-1'])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

        $this->assertSame(0, AssetTag::query()->count());
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

<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Models\AppUser;
use App\Models\Equipment;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AssetCreationTest extends TestCase
{
    private const FIELDS = ['eqm_prc_code', 'eqm_name', 'eqm_is_active'];

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();

        DatabaseSchema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        DatabaseSchema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'manager', 'technician'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        Permission::findOrCreate('Create:EquipmentResource', 'web');
    }

    private function makeUser(string $role, bool $withCreatePermission = false): AppUser
    {
        $user = AppUser::create([
            'user_fname' => 'Test',
            'user_lname' => ucfirst($role),
            'user_email' => fake()->unique()->safeEmail(),
            'is_active' => true,
        ]);

        $user->assignRole($role);

        if ($withCreatePermission) {
            $user->givePermissionTo('Create:EquipmentResource');
        }

        return $user;
    }

    /**
     * @return array<string, TextInput|Toggle>
     */
    private function assetFields(string $operation): array
    {
        $livewire = new class extends Component implements HasSchemas
        {
            use InteractsWithSchemas;

            public function render(): string
            {
                return '';
            }
        };

        $schema = EquipmentForm::configure(Schema::make($livewire)->operation($operation));

        return collect($schema->getFlatComponents(withHidden: true))
            ->filter(fn ($component): bool => $component instanceof TextInput || $component instanceof Toggle)
            ->filter(fn ($component): bool => in_array($component->getName(), self::FIELDS, true))
            ->keyBy(fn ($component): string => $component->getName())
            ->all();
    }

    public function test_only_super_admins_can_add_assets_by_default(): void
    {
        $this->assertTrue(Gate::forUser($this->makeUser('super_admin'))->allows('create', Equipment::class));
        $this->assertFalse(Gate::forUser($this->makeUser('manager'))->allows('create', Equipment::class));
        $this->assertFalse(Gate::forUser($this->makeUser('technician'))->allows('create', Equipment::class));
    }

    public function test_any_role_can_add_assets_once_the_create_permission_is_ticked_for_it(): void
    {
        foreach (['manager', 'technician'] as $role) {
            $this->assertTrue(Gate::forUser($this->makeUser($role, withCreatePermission: true))->allows('create', Equipment::class), $role);
        }
    }

    public function test_other_permissions_do_not_allow_adding_assets(): void
    {
        $manager = $this->makeUser('manager');
        $manager->givePermissionTo(Permission::findOrCreate('Update:EquipmentResource', 'web'));
        $manager->givePermissionTo(Permission::findOrCreate('ViewAny:EquipmentResource', 'web'));

        $this->assertFalse(Gate::forUser($manager)->allows('create', Equipment::class));
    }

    public function test_the_code_name_and_active_fields_can_be_entered_when_creating_an_asset(): void
    {
        $fields = $this->assetFields('create');

        $this->assertSame(self::FIELDS, array_keys($fields));

        foreach ($fields as $name => $field) {
            $this->assertFalse($field->isDisabled(), "{$name} must be editable on create");
            $this->assertTrue($field->isDehydrated(), "{$name} must be saved on create");
        }

        $this->assertTrue($fields['eqm_prc_code']->isRequired());
        $this->assertTrue($fields['eqm_name']->isRequired());
        $this->assertTrue($fields['eqm_is_active']->getDefaultState());
    }

    public function test_the_code_name_and_active_fields_are_locked_and_never_saved_when_a_non_super_admin_edits(): void
    {
        $manager = $this->makeUser('manager');
        $manager->givePermissionTo(Permission::findOrCreate('Update:EquipmentResource', 'web'));
        $this->actingAs($manager);

        foreach ($this->assetFields('edit') as $name => $field) {
            $this->assertTrue($field->isDisabled(), "{$name} must be read-only on edit");
            $this->assertFalse($field->isDehydrated(), "{$name} must not be saved on edit");
        }
    }

    public function test_only_a_super_admin_can_change_the_code_name_and_active_fields_when_editing(): void
    {
        $this->actingAs($this->makeUser('super_admin'));

        foreach ($this->assetFields('edit') as $name => $field) {
            $this->assertFalse($field->isDisabled(), "{$name} must be editable by a super admin");
            $this->assertTrue($field->isDehydrated(), "{$name} must be saved when a super admin edits");
        }
    }

    public function test_the_fields_stay_editable_on_create_for_a_user_who_may_create_assets(): void
    {
        $this->actingAs($this->makeUser('manager', withCreatePermission: true));

        foreach ($this->assetFields('create') as $name => $field) {
            $this->assertFalse($field->isDisabled(), "{$name}");
            $this->assertTrue($field->isDehydrated(), "{$name}");
        }
    }
}

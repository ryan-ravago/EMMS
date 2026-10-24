<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\AppUser;
use App\Models\AssetType;
use App\Models\Equipment;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AssetPagesTest extends TestCase
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
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
        });

        AssetType::forceCreate(['id' => 5, 'code' => 'EQM', 'name' => 'Equipment']);
        AssetType::forceCreate(['id' => 8, 'code' => 'ACC', 'name' => 'Accessory']);
        AssetType::forceCreate(['id' => 3, 'code' => 'SRVV', 'name' => 'Service Vehicle']);
        AssetType::flushIdCache();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'manager'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }
    }

    protected function tearDown(): void
    {
        AssetType::flushIdCache();

        parent::tearDown();
    }

    /**
     * @param  list<string>  $permissions
     */
    private function makeUser(string $role, array $permissions = []): AppUser
    {
        $user = AppUser::create([
            'user_fname' => 'Test',
            'user_lname' => ucfirst($role),
            'user_email' => fake()->unique()->safeEmail(),
            'is_active' => true,
        ]);

        $user->assignRole($role);

        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $user;
    }

    /**
     * @return list<string>
     */
    private function visiblePageLabels(AppUser $user): array
    {
        $this->actingAs($user);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Filament::setCurrentPanel('admin');

        return collect(Filament::getCurrentPanel()->getNavigationItems())
            ->filter(fn (NavigationItem $item): bool => $item->isVisible())
            ->map(fn (NavigationItem $item): string => $item->getLabel())
            ->intersect(['Assets', 'Equipment', 'Accessories', 'Service Vehicles'])
            ->values()
            ->all();
    }

    public function test_the_four_asset_pages_are_routed_and_the_unconfigured_page_is_not(): void
    {
        Filament::setCurrentPanel('admin');

        $this->assertFalse(Route::has('filament.admin.resources.asset.index'));
        $this->assertFalse(Route::has('filament.admin.resources.asset.view'));

        foreach (['assets', 'equipment', 'accessories', 'service-vehicles'] as $page) {
            foreach (['index', 'create', 'view', 'edit'] as $name) {
                $this->assertTrue(Route::has("filament.admin.resources.{$page}.{$name}"), "{$page}.{$name}");
            }
        }
    }

    public function test_a_super_admin_sees_all_four_pages(): void
    {
        $this->assertSame(
            ['Assets', 'Equipment', 'Accessories', 'Service Vehicles'],
            $this->visiblePageLabels($this->makeUser('super_admin')),
        );
    }

    public function test_a_user_without_the_page_permissions_sees_none_of_the_pages(): void
    {
        $manager = $this->makeUser('manager', ['ViewAny:EquipmentResource', 'View:EquipmentResource']);

        $this->assertSame([], $this->visiblePageLabels($manager));

        foreach (array_keys(EquipmentResource::PAGES) as $page) {
            $this->assertFalse(EquipmentResource::canViewPage($page), $page);
        }
    }

    public function test_each_page_is_shown_only_with_its_own_permission(): void
    {
        $this->assertSame(['Assets'], $this->visiblePageLabels($this->makeUser('manager', ['ViewAssetList:EquipmentResource'])));
        $this->assertSame(['Equipment'], $this->visiblePageLabels($this->makeUser('manager', ['ViewEquipmentList:EquipmentResource'])));
        $this->assertSame(['Accessories'], $this->visiblePageLabels($this->makeUser('manager', ['ViewAccessoryList:EquipmentResource'])));
        $this->assertSame(['Service Vehicles'], $this->visiblePageLabels($this->makeUser('manager', ['ViewServiceVehicleList:EquipmentResource'])));
        $this->assertSame(
            ['Equipment', 'Service Vehicles'],
            $this->visiblePageLabels($this->makeUser('manager', ['ViewEquipmentList:EquipmentResource', 'ViewServiceVehicleList:EquipmentResource'])),
        );
    }

    public function test_opening_an_asset_needs_the_permission_of_its_own_page(): void
    {
        $manager = $this->makeUser('manager', ['View:EquipmentResource', 'ViewEquipmentList:EquipmentResource']);

        $equipment = new Equipment(['asset_type_id' => 5]);
        $accessory = new Equipment(['asset_type_id' => 8]);
        $serviceVehicle = new Equipment(['asset_type_id' => 3]);
        $untyped = new Equipment;

        $this->assertTrue(Gate::forUser($manager)->allows('view', $equipment));
        $this->assertFalse(Gate::forUser($manager)->allows('view', $accessory));
        $this->assertFalse(Gate::forUser($manager)->allows('view', $serviceVehicle));
        $this->assertTrue(Gate::forUser($manager)->allows('view', $untyped));

        $this->assertTrue(Gate::forUser($this->makeUser('super_admin'))->allows('view', $serviceVehicle));
    }

    public function test_the_assets_page_permission_opens_assets_of_every_type(): void
    {
        $manager = $this->makeUser('manager', ['View:EquipmentResource', 'ViewAssetList:EquipmentResource']);

        foreach ([5, 8, 3] as $typeId) {
            $this->assertTrue(Gate::forUser($manager)->allows('view', new Equipment(['asset_type_id' => $typeId])), (string) $typeId);
        }
    }

    public function test_links_on_the_assets_page_stay_on_it_and_untyped_assets_link_to_it(): void
    {
        Filament::setCurrentPanel('admin');

        $equipment = (new Equipment)->forceFill(['eqm_id' => 11, 'asset_type_id' => 5]);
        $untyped = (new Equipment)->forceFill(['eqm_id' => 14, 'asset_type_id' => null]);

        $this->assertStringEndsWith('/assets/11', EquipmentResource::withConfiguration('assets', fn (): string => EquipmentResource::getUrl('view', ['record' => $equipment])));
        $this->assertStringEndsWith('/assets/14', EquipmentResource::getUrl('view', ['record' => $untyped]));
        $this->assertStringEndsWith('/assets', EquipmentResource::withConfiguration('assets', fn (): string => EquipmentResource::getUrl()));
    }

    public function test_links_outside_a_page_go_to_the_page_of_the_assets_type(): void
    {
        Filament::setCurrentPanel('admin');

        $equipment = (new Equipment)->forceFill(['eqm_id' => 11, 'asset_type_id' => 5]);
        $accessory = (new Equipment)->forceFill(['eqm_id' => 12, 'asset_type_id' => 8]);
        $serviceVehicle = (new Equipment)->forceFill(['eqm_id' => 13, 'asset_type_id' => 3]);

        $this->assertStringEndsWith('/equipment/11', EquipmentResource::getUrl('view', ['record' => $equipment]));
        $this->assertStringEndsWith('/accessories/12', EquipmentResource::getUrl('view', ['record' => $accessory]));
        $this->assertStringEndsWith('/service-vehicles/13', EquipmentResource::getUrl('view', ['record' => $serviceVehicle]));
        $this->assertStringEndsWith('/equipment', EquipmentResource::getUrl());
    }

    public function test_the_migration_gives_existing_asset_viewers_every_page(): void
    {
        $viewer = Role::create(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->givePermissionTo(Permission::findOrCreate('ViewAny:EquipmentResource', 'web'));
        $other = Role::create(['name' => 'other', 'guard_name' => 'web']);

        (require base_path('database/migrations/2026_10_23_151753_add_asset_page_permissions.php'))->up();

        $this->assertSame(3, DB::table('permissions')->where('name', 'like', 'View%List:EquipmentResource')->count());
        $this->assertEqualsCanonicalizing(
            ['ViewAny:EquipmentResource', 'ViewEquipmentList:EquipmentResource', 'ViewAccessoryList:EquipmentResource', 'ViewServiceVehicleList:EquipmentResource'],
            $viewer->fresh()->permissions->pluck('name')->all(),
        );
        $this->assertSame([], $other->fresh()->permissions->pluck('name')->all());
    }

    public function test_the_migration_gives_existing_asset_viewers_the_assets_page(): void
    {
        $viewer = Role::create(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->givePermissionTo(Permission::findOrCreate('ViewAny:EquipmentResource', 'web'));
        $other = Role::create(['name' => 'other', 'guard_name' => 'web']);

        (require base_path('database/migrations/2026_10_24_101933_add_asset_list_page_permission.php'))->up();

        $this->assertTrue($viewer->fresh()->hasPermissionTo('ViewAssetList:EquipmentResource'));
        $this->assertFalse($other->fresh()->hasPermissionTo('ViewAssetList:EquipmentResource'));
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Resources\AssetTypes\AssetTypeResource;
use App\Filament\Resources\AssetTypes\RelationManagers\AssetsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\AccessoriesRelationManager;
use App\Models\AssetType;
use App\Models\Equipment;
use App\Models\LifecycleLog;
use App\Observers\EquipmentObserver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

class EquipmentAccessoryAllocationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

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

        Schema::create('lifecycle_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('action_id');
            $table->string('status_id');
            $table->unsignedBigInteger('deploy_to_loc_id')->nullable();
            $table->unsignedBigInteger('allocate_to_equipment_id')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('logged_at');
        });

        AssetType::flushIdCache();
    }

    public function test_accessory_can_be_allocated_to_equipment(): void
    {
        $equipment = Equipment::factory()->equipment()->create([
            'eqm_name' => 'Dump Truck',
        ]);
        $accessory = Equipment::factory()->accessory($equipment)->create([
            'eqm_name' => 'GPS Tracker',
        ]);

        $this->assertSame($equipment->eqm_id, $accessory->fresh()->parent_id);
        $this->assertTrue($accessory->isAccessory());
        $this->assertTrue($equipment->isEquipmentAsset());
        $this->assertTrue($equipment->accessories->contains('eqm_id', $accessory->eqm_id));
        $this->assertTrue($accessory->parent->is($equipment));
    }

    public function test_accessory_allocation_creates_an_allocated_lifecycle_log(): void
    {
        $equipment = Equipment::factory()->equipment()->create();
        $accessory = Equipment::factory()->accessory()->create();

        $this->invokeLifecycleLogMethod(
            'createLifecycleLog',
            $accessory,
            'alc',
            'alc',
            $equipment->eqm_id,
        );

        $this->assertDatabaseHas('lifecycle_logs', [
            'asset_id' => $accessory->eqm_id,
            'action_id' => 'alc',
            'status_id' => 'alc',
            'allocate_to_equipment_id' => $equipment->eqm_id,
        ]);
    }

    public function test_bulk_accessory_unallocation_creates_one_idle_log_per_accessory(): void
    {
        $equipment = Equipment::factory()->equipment()->create();
        $accessories = Equipment::factory()
            ->count(2)
            ->accessory($equipment)
            ->create();

        $this->invokeLifecycleLogMethod(
            'createLifecycleLogs',
            $accessories->pluck('eqm_id')->all(),
            'sidle',
            'idle',
        );

        $this->assertSame(2, LifecycleLog::query()->count());
        $this->assertSame(2, LifecycleLog::query()
            ->where('action_id', 'sidle')
            ->where('status_id', 'idle')
            ->whereNull('allocate_to_equipment_id')
            ->count());
    }

    public function test_equipment_cannot_keep_a_parent_allocation(): void
    {
        $parent = Equipment::factory()->equipment()->create([
            'eqm_name' => 'Parent Unit',
        ]);
        $equipment = Equipment::factory()->equipment()->create([
            'eqm_name' => 'Child Equipment',
            'parent_id' => $parent->eqm_id,
        ]);

        $this->assertNull($equipment->fresh()->parent_id);
    }

    public function test_accessory_cannot_be_allocated_to_another_accessory(): void
    {
        $equipment = Equipment::factory()->equipment()->create();
        $accessoryParent = Equipment::factory()->accessory($equipment)->create();
        $accessory = Equipment::factory()->accessory($accessoryParent)->create();

        $this->assertNull($accessory->fresh()->parent_id);
    }

    public function test_accessories_relation_manager_is_only_visible_for_equipment(): void
    {
        $equipment = Equipment::factory()->equipment()->create();
        $accessory = Equipment::factory()->accessory()->create();

        $this->assertTrue($equipment->isEquipmentAsset());
        $this->assertFalse($accessory->isEquipmentAsset());
        $this->assertFalse(AccessoriesRelationManager::canViewForRecord($accessory, 'view'));
    }

    public function test_asset_type_resource_registers_the_assets_relation_manager(): void
    {
        $this->assertContains(AssetsRelationManager::class, AssetTypeResource::getRelations());
    }

    public function test_observer_clears_self_parent(): void
    {
        $accessory = Equipment::factory()->accessory()->create();
        $accessory->parent_id = $accessory->eqm_id;

        (new EquipmentObserver)->saving($accessory);

        $this->assertNull($accessory->parent_id);
    }

    private function invokeLifecycleLogMethod(string $method, mixed ...$arguments): void
    {
        $reflection = new ReflectionClass(AccessoriesRelationManager::class);
        $manager = $reflection->newInstanceWithoutConstructor();
        $lifecycleLogMethod = $reflection->getMethod($method);
        $lifecycleLogMethod->invoke($manager, ...$arguments);
    }
}

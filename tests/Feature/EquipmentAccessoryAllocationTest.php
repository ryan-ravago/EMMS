<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\RelationManagers\AccessoriesRelationManager;
use App\Models\AssetType;
use App\Models\Equipment;
use App\Observers\EquipmentObserver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

    public function test_observer_clears_self_parent(): void
    {
        $accessory = Equipment::factory()->accessory()->create();
        $accessory->parent_id = $accessory->eqm_id;

        (new EquipmentObserver)->saving($accessory);

        $this->assertNull($accessory->parent_id);
    }
}

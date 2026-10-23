<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\Equipment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssetTypeCodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        AssetType::flushIdCache();
    }

    protected function tearDown(): void
    {
        AssetType::flushIdCache();

        parent::tearDown();
    }

    private function createLegacyAssetTypesTable(): void
    {
        Schema::create('asset_types', function ($table): void {
            $table->id();
            $table->string('name')->unique();
        });
    }

    private function runCodeMigration(): void
    {
        (require base_path('database/migrations/2026_10_23_150315_add_code_to_asset_types_table.php'))->up();
    }

    public function test_the_migration_gives_every_existing_type_its_code(): void
    {
        $this->createLegacyAssetTypesTable();

        DB::table('asset_types')->insert([
            ['id' => 1, 'name' => 'Equipment'],
            ['id' => 2, 'name' => 'Accessory'],
            ['id' => 3, 'name' => 'Service Vehicle'],
            ['id' => 4, 'name' => 'Something Else'],
        ]);

        $this->runCodeMigration();

        $this->assertSame(
            ['Equipment' => 'EQM', 'Accessory' => 'ACC', 'Service Vehicle' => 'SRVV', 'Something Else' => 'TYPE4'],
            DB::table('asset_types')->pluck('code', 'name')->all(),
        );
    }

    public function test_the_code_is_unique_and_required(): void
    {
        $this->createLegacyAssetTypesTable();
        $this->runCodeMigration();

        AssetType::create(['code' => 'EQM', 'name' => 'Equipment']);

        try {
            AssetType::create(['code' => 'EQM', 'name' => 'Another']);
            $this->fail('A duplicate code must be rejected.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }

        try {
            DB::table('asset_types')->insert(['name' => 'No Code']);
            $this->fail('A type without a code must be rejected.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }

    public function test_types_are_found_by_code_whatever_their_id_is(): void
    {
        $this->createLegacyAssetTypesTable();
        $this->runCodeMigration();

        AssetType::forceCreate(['id' => 4, 'code' => 'SRVV', 'name' => 'Service Vehicle']);
        AssetType::forceCreate(['id' => 7, 'code' => 'ACC', 'name' => 'Accessory']);
        AssetType::forceCreate(['id' => 9, 'code' => 'EQM', 'name' => 'Equipment']);

        $this->assertSame(9, AssetType::equipmentId());
        $this->assertSame(7, AssetType::accessoryId());
        $this->assertSame(4, AssetType::serviceVehicleId());
        $this->assertNull(AssetType::idFor('NOPE'));

        $this->assertTrue((new Equipment(['asset_type_id' => 9]))->isEquipmentAsset());
        $this->assertFalse((new Equipment(['asset_type_id' => 9]))->isAccessory());
        $this->assertTrue((new Equipment(['asset_type_id' => 7]))->isAccessory());
        $this->assertFalse((new Equipment(['asset_type_id' => 4]))->isEquipmentAsset());
        $this->assertFalse((new Equipment(['asset_type_id' => 4]))->isAccessory());
    }

    public function test_the_type_helpers_use_the_code_not_the_name(): void
    {
        $this->assertTrue((new AssetType(['code' => 'EQM', 'name' => 'Anything']))->isEquipment());
        $this->assertTrue((new AssetType(['code' => 'ACC', 'name' => 'Anything']))->isAccessory());
        $this->assertTrue((new AssetType(['code' => 'SRVV', 'name' => 'Anything']))->isServiceVehicle());
        $this->assertFalse((new AssetType(['code' => 'ACC', 'name' => 'Equipment']))->isEquipment());
    }

    public function test_the_equipment_and_accessory_scopes_filter_on_the_code(): void
    {
        $equipmentSql = Equipment::query()->equipmentAssets()->toSql();
        $accessorySql = Equipment::query()->accessories()->toSql();

        $this->assertStringContainsString('"code" = ?', $equipmentSql);
        $this->assertStringContainsString('"code" = ?', $accessorySql);
        $this->assertSame(['EQM'], Equipment::query()->equipmentAssets()->getBindings());
        $this->assertSame(['ACC'], Equipment::query()->accessories()->getBindings());
    }
}

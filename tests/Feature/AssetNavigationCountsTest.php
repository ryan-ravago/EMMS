<?php

namespace Tests\Feature;

use App\Models\AssetType;
use App\Models\Equipment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssetNavigationCountsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('eqm_eqmm_id')->nullable();
            $table->string('eqm_name');
            $table->string('eqm_prc_code')->nullable()->unique();
            $table->boolean('eqm_is_active')->default(true);
        });

        AssetType::flushIdCache();
        Cache::forget(Equipment::ASSET_COUNTS_CACHE_KEY);
    }

    protected function tearDown(): void
    {
        AssetType::flushIdCache();
        Cache::forget(Equipment::ASSET_COUNTS_CACHE_KEY);

        parent::tearDown();
    }

    public function test_counts_are_zero_when_there_are_no_assets(): void
    {
        $this->assertSame(
            ['total' => 0, 'EQM' => 0, 'ACC' => 0, 'SRVV' => 0],
            Equipment::assetCounts(),
        );
    }

    public function test_counts_are_grouped_by_asset_type_and_the_total_includes_untyped_assets(): void
    {
        $equipment = Equipment::factory()->equipment()->create();
        Equipment::factory()->equipment()->create();
        Equipment::factory()->accessory($equipment)->create();
        Equipment::factory()->serviceVehicle()->create();
        DB::table('equipment_units')->insert(['eqm_name' => 'Untyped', 'asset_type_id' => null]);

        Cache::forget(Equipment::ASSET_COUNTS_CACHE_KEY);

        $this->assertSame(
            ['total' => 5, 'EQM' => 2, 'ACC' => 1, 'SRVV' => 1],
            Equipment::assetCounts(),
        );
    }

    public function test_all_counts_are_read_with_a_single_query(): void
    {
        Equipment::factory()->equipment()->create();
        Cache::forget(Equipment::ASSET_COUNTS_CACHE_KEY);

        DB::enableQueryLog();
        Equipment::assetCounts();
        Equipment::assetCounts();
        Equipment::assetCounts();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(1, $queries);
    }

    public function test_counts_refresh_when_an_asset_is_saved_or_deleted(): void
    {
        $this->assertSame(0, Equipment::assetCounts()['EQM']);

        $equipment = Equipment::factory()->equipment()->create();
        $this->assertSame(1, Equipment::assetCounts()['EQM']);
        $this->assertSame(1, Equipment::assetCounts()['total']);

        $equipment->delete();
        $this->assertSame(0, Equipment::assetCounts()['EQM']);
        $this->assertSame(0, Equipment::assetCounts()['total']);
    }
}

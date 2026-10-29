<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LocationPathMapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
        });

        // Deleting a location checks what still points at it.
        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->unsignedBigInteger('location_id')->nullable();
        });

        Schema::create('asset_tag_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('yard_id')->nullable();
        });
    }

    /**
     * @return array{0: Location, 1: Location, 2: Location, 3: Location}
     */
    private function makeChain(): array
    {
        $site = Location::create(['name' => 'Site']);
        $yard = Location::create(['name' => 'Yard', 'parent_id' => $site->id]);
        $bay = Location::create(['name' => 'Bay', 'parent_id' => $yard->id]);
        $slot = Location::create(['name' => 'Slot 1', 'parent_id' => $bay->id]);

        return [$site, $yard, $bay, $slot];
    }

    public function test_full_path_is_built_at_any_depth(): void
    {
        [$site, , , $slot] = $this->makeChain();

        $this->assertSame('Site', $site->full_path);
        $this->assertSame('Site > Yard > Bay > Slot 1', $slot->fresh()->full_path);
        $this->assertSame('Site > Yard > Bay', $slot->fresh()->parent_path);
        $this->assertSame('', $site->fresh()->parent_path);
    }

    public function test_paths_for_many_locations_use_one_query(): void
    {
        $this->makeChain();
        Location::flushPathMap();
        $locations = Location::all();

        DB::enableQueryLog();
        $paths = $locations->map(fn (Location $location): string => $location->full_path)->all();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertSame(['Site', 'Site > Yard', 'Site > Yard > Bay', 'Site > Yard > Bay > Slot 1'], $paths);
        $this->assertCount(1, $queries);
    }

    public function test_renaming_a_location_updates_the_paths_below_it(): void
    {
        [, $yard, , $slot] = $this->makeChain();
        $this->assertSame('Site > Yard > Bay > Slot 1', $slot->fresh()->full_path);

        $yard->update(['name' => 'North Yard']);

        $this->assertSame('Site > North Yard > Bay > Slot 1', $slot->fresh()->full_path);
    }

    public function test_moving_a_location_updates_its_path(): void
    {
        [$site, , $bay] = $this->makeChain();
        $other = Location::create(['name' => 'Annex']);
        $this->assertSame('Site > Yard > Bay', $bay->fresh()->full_path);

        $bay->update(['parent_id' => $other->id]);

        $this->assertSame('Annex > Bay', $bay->fresh()->full_path);
        $this->assertSame('Site', $site->fresh()->full_path);
    }

    public function test_deleting_a_location_refreshes_the_map(): void
    {
        $site = Location::create(['name' => 'Site']);
        $yard = Location::create(['name' => 'Yard', 'parent_id' => $site->id]);
        $this->assertArrayHasKey($yard->id, Location::pathMap());

        $yard->delete();

        $this->assertArrayNotHasKey($yard->id, Location::pathMap());
    }

    public function test_unsaved_changes_are_reflected_in_the_path(): void
    {
        [, $yard] = $this->makeChain();
        $yard = $yard->fresh();
        $this->assertSame('Site > Yard', $yard->full_path);

        $yard->name = 'Renamed';

        $this->assertSame('Site > Renamed', $yard->full_path);
    }

    public function test_a_loop_in_bad_data_does_not_recurse_forever(): void
    {
        $a = Location::create(['name' => 'A']);
        $b = Location::create(['name' => 'B', 'parent_id' => $a->id]);
        DB::table('locations')->where('id', $a->id)->update(['parent_id' => $b->id]);
        Location::flushPathMap();

        $this->assertIsString(Location::pathMap()[$a->id]);
        $this->assertIsString(Location::pathMap()[$b->id]);
    }
}

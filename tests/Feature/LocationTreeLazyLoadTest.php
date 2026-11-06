<?php

namespace Tests\Feature;

use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Models\Location;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class LocationTreeLazyLoadTest extends AssetMovementTestCase
{
    private Location $plant;

    private Location $warehouse;

    private Location $bay;

    private Location $office;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Filament::setCurrentPanel('admin');

        // Plant > Warehouse > Bay, and a separate Office.
        $this->plant = Location::create(['name' => 'Plant']);
        $this->warehouse = Location::create(['name' => 'Warehouse', 'parent_id' => $this->plant->id]);
        $this->bay = Location::create(['name' => 'Bay', 'parent_id' => $this->warehouse->id]);
        $this->office = Location::create(['name' => 'Office']);

        $user = $this->makeUser('Super', 'Admin')->refresh();
        $user->assignRole('super_admin');
        $this->actingAs($user);
    }

    public function test_only_the_top_level_is_loaded_until_a_branch_is_opened(): void
    {
        Livewire::test(ListLocations::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$this->plant, $this->office])
            ->assertCanNotSeeTableRecords([$this->warehouse, $this->bay]);
    }

    public function test_opening_a_branch_loads_only_its_next_level(): void
    {
        Livewire::test(ListLocations::class)
            ->loadTable()
            ->call('toggleTreeRecord', $this->plant->id)
            ->assertCanSeeTableRecords([$this->plant, $this->warehouse, $this->office])
            ->assertCanNotSeeTableRecords([$this->bay])
            ->call('toggleTreeRecord', $this->warehouse->id)
            ->assertCanSeeTableRecords([$this->bay])
            ->call('toggleTreeRecord', $this->plant->id)
            ->assertCanNotSeeTableRecords([$this->warehouse, $this->bay]);
    }

    public function test_expand_all_and_collapse_all_still_work(): void
    {
        Livewire::test(ListLocations::class)
            ->loadTable()
            ->call('expandAllTreeRecords')
            ->assertCanSeeTableRecords([$this->plant, $this->warehouse, $this->bay, $this->office])
            ->call('collapseAllTreeRecords')
            ->assertCanSeeTableRecords([$this->plant, $this->office])
            ->assertCanNotSeeTableRecords([$this->warehouse, $this->bay]);
    }

    public function test_a_search_finds_a_deep_location_and_shows_it_under_its_parents(): void
    {
        Livewire::test(ListLocations::class)
            ->loadTable()
            ->searchTable('Bay')
            ->assertCanSeeTableRecords([$this->plant, $this->warehouse, $this->bay])
            ->assertCanNotSeeTableRecords([$this->office])
            ->searchTable('')
            ->assertCanSeeTableRecords([$this->plant, $this->office])
            ->assertCanNotSeeTableRecords([$this->warehouse, $this->bay]);
    }

    public function test_a_search_with_no_match_shows_nothing(): void
    {
        Livewire::test(ListLocations::class)
            ->loadTable()
            ->searchTable('does not exist')
            ->assertCanNotSeeTableRecords([$this->plant, $this->warehouse, $this->bay, $this->office]);
    }

    public function test_a_location_whose_parent_is_missing_is_shown_at_the_top(): void
    {
        DB::table('locations')->where('id', $this->warehouse->id)->update(['parent_id' => 9999]);

        Livewire::test(ListLocations::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$this->plant, $this->warehouse, $this->office]);
    }

    public function test_a_closed_branch_does_not_load_its_children(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            if (str_contains($query->sql, 'from "locations"') && str_contains($query->sql, 'select *')) {
                $queries[] = $query->bindings;
            }
        });

        Livewire::test(ListLocations::class)->loadTable();

        // Full rows are only asked for the two top-level locations, never for the whole tree.
        $loadedIds = collect($queries)->flatten()->map(fn ($id) => (int) $id)->all();
        $this->assertNotContains($this->warehouse->id, $loadedIds);
        $this->assertNotContains($this->bay->id, $loadedIds);
    }
}

<?php

namespace Tests\Feature;

use App\Models\AssetType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportAccessoriesTest extends TestCase
{
    private string $storage;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        $this->storage = sys_get_temp_dir().'/emms-accessories-test-'.uniqid();
        File::ensureDirectoryExists($this->storage.'/app');
        $this->app->useStoragePath($this->storage);

        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->unsignedBigInteger('asset_type_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('lifecycle_status_id')->default('idle');
            $table->string('eqm_prc_code', 30)->unique();
            $table->string('eqm_name');
            $table->boolean('eqm_is_active')->default(true);
            $table->text('specifications')->nullable();
            $table->unsignedBigInteger('eqm_eqmm_id')->nullable();
            $table->unsignedBigInteger('eqm_eqmt_id')->nullable();
        });

        DB::table('asset_types')->insert([
            ['id' => 1, 'code' => 'EQM', 'name' => 'Equipment'],
            ['id' => 2, 'code' => 'ACC', 'name' => 'Accessory'],
            ['id' => 3, 'code' => 'SRVV', 'name' => 'Service Vehicle'],
        ]);
        AssetType::flushIdCache();

        DB::table('equipment_units')->insert(['eqm_prc_code' => 'MAIN HOOK', 'eqm_name' => 'Main Hook', 'asset_type_id' => 2]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->storage);
        AssetType::flushIdCache();

        parent::tearDown();
    }

    private function csv(string $content): string
    {
        $path = $this->storage.'/accessories_'.uniqid().'.csv';
        File::put($path, $content);

        return $path;
    }

    private function sample(): string
    {
        return $this->csv(implode("\n", [
            'NO.,ACCESSORY NAME',
            '1,MAIN HOOK',
            '2,MAIN HOOK (SINGLE SHEAVE)',
            '3,MAIN HOOK(4SHEAVES)',
            '4,"  JIB   EXTENSION  "',
            '5,main  hook (single sheave)',
            '6,THIS ACCESSORY NAME IS WAY TOO LONG FOR A CODE',
            '7,',
            ',',
        ])."\n");
    }

    public function test_preview_changes_nothing(): void
    {
        $this->artisan('assets:import-accessories', ['source' => $this->sample()])
            ->expectsOutputToContain('Preview only')
            ->assertSuccessful();

        $this->assertSame(1, DB::table('equipment_units')->count());
    }

    public function test_execute_adds_each_name_as_an_active_accessory_without_a_parent(): void
    {
        $this->artisan('assets:import-accessories', ['source' => $this->sample(), '--execute' => true, '--force' => true])
            ->assertSuccessful();

        $added = DB::table('equipment_units')->where('eqm_prc_code', '!=', 'MAIN HOOK')->orderBy('eqm_prc_code')->get();

        $this->assertSame(['JIB EXTENSION', 'MAIN HOOK (SINGLE SHEAVE)', 'MAIN HOOK(4SHEAVES)'], $added->pluck('eqm_prc_code')->all());
        $this->assertSame($added->pluck('eqm_prc_code')->all(), $added->pluck('eqm_name')->all());
        $this->assertTrue($added->every(fn ($a) => (int) $a->asset_type_id === 2 && $a->parent_id === null && $a->location_id === null && (bool) $a->eqm_is_active));
        $this->assertCount(1, File::glob($this->storage.'/app/asset-imports/*_added_accessories.csv'));
    }

    public function test_existing_repeated_and_long_names_are_skipped_and_empty_rows_ignored(): void
    {
        $this->artisan('assets:import-accessories', ['source' => $this->sample()])
            ->expectsOutputToContain('(as MAIN HOOK)')
            ->expectsOutputToContain('listed twice in the file')
            ->expectsOutputToContain('name is longer than 30 characters')
            ->doesntExpectOutputToContain('no accessory name')
            ->assertSuccessful();
    }

    public function test_only_the_accessory_name_column_is_used_wherever_it_is(): void
    {
        $path = $this->csv("Remarks,accessory name,NO.\nignored,SPREADER BAR,1\n");

        $this->artisan('assets:import-accessories', ['source' => $path, '--execute' => true, '--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('equipment_units', ['eqm_prc_code' => 'SPREADER BAR', 'asset_type_id' => 2]);
    }

    public function test_a_file_without_the_column_fails(): void
    {
        $this->artisan('assets:import-accessories', ['source' => $this->csv("NO.,NAME\n1,HOOK\n")])
            ->expectsOutputToContain('no "ACCESSORY NAME" column')
            ->assertFailed();
    }

    public function test_a_missing_file_fails(): void
    {
        $this->artisan('assets:import-accessories', ['source' => $this->storage.'/nope.csv'])->assertFailed();
    }

    public function test_running_it_again_adds_nothing(): void
    {
        $this->artisan('assets:import-accessories', ['source' => $this->sample(), '--execute' => true, '--force' => true])->assertSuccessful();
        $this->artisan('assets:import-accessories', ['source' => $this->sample(), '--execute' => true, '--force' => true])
            ->expectsOutputToContain('Nothing to add')
            ->assertSuccessful();

        $this->assertSame(4, DB::table('equipment_units')->count());
    }

    public function test_declining_the_confirmation_adds_nothing(): void
    {
        $this->artisan('assets:import-accessories', ['source' => $this->sample(), '--execute' => true])
            ->expectsConfirmation('Add 3 accessories to EMMS?', 'no')
            ->assertFailed();

        $this->assertSame(1, DB::table('equipment_units')->count());
    }
}

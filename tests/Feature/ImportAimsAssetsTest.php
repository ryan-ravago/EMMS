<?php

namespace Tests\Feature;

use App\Models\AssetType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportAimsAssetsTest extends TestCase
{
    private string $storage;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        // Reports are written under storage/app; keep the test's own away from the real ones.
        $this->storage = sys_get_temp_dir().'/emms-aims-assets-test-'.uniqid();
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

        DB::table('equipment_units')->insert(['eqm_prc_code' => 'MD   01', 'eqm_name' => 'Drop Deck Trailer', 'asset_type_id' => 2]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->storage);
        AssetType::flushIdCache();

        parent::tearDown();
    }

    private function csv(string $content): string
    {
        $path = $this->storage.'/assets_'.uniqid().'.csv';
        File::put($path, $content);

        return $path;
    }

    private function sample(): string
    {
        return $this->csv(implode("\n", [
            'Asset Code,Asset Name,Asset Type,AIMS Table,AIMS ID,Parent (AIMS),Remarks (AIMS)',
            'L300#3,L300#3,Service Vehicle,PARENT,1009,,TOK 581',
            'TOWBAR #1,TOWBAR #1,Accessory,ACCESSORIES,8000,TRANSPORT EQUIPMENT,1803-233 (3M)',
            'XE215D,XE215D,Equipment,PARENT,13507,,',
            'MD 01,MD 01,Accessory,ACCESSORIES,4404,FAYMONVILLE,already there',
            'BOOM SECTION 12M (Serial No: 2724-22-13),BOOM SECTION 12M (Serial No: 2724-22-13),Accessory,ACCESSORIES,1,CC2800 #1,',
            'DUMMY 1,DUMMY 1,,ACCESSORIES,2,DUMMY PARENT,',
            'l300 #3,l300 #3,Service Vehicle,PARENT,1009,,listed twice',
        ])."\n");
    }

    public function test_preview_changes_nothing(): void
    {
        $this->artisan('assets:import-aims', ['source' => $this->sample()])
            ->expectsOutputToContain('Preview only')
            ->assertSuccessful();

        $this->assertSame(1, DB::table('equipment_units')->count());
    }

    public function test_execute_adds_the_new_assets_with_their_type_and_aims_remarks_and_no_parent(): void
    {
        $this->artisan('assets:import-aims', ['source' => $this->sample(), '--execute' => true, '--force' => true])
            ->assertSuccessful();

        $added = DB::table('equipment_units')->where('eqm_prc_code', '!=', 'MD   01')->orderBy('eqm_prc_code')->get()->keyBy('eqm_prc_code');

        $this->assertSame(['L300#3', 'TOWBAR #1', 'XE215D'], $added->keys()->all());
        $this->assertSame(3, (int) $added['L300#3']->asset_type_id);
        $this->assertSame(2, (int) $added['TOWBAR #1']->asset_type_id);
        $this->assertSame(1, (int) $added['XE215D']->asset_type_id);
        $this->assertSame('<p>AIMS remarks: TOK 581</p>', $added['L300#3']->specifications);
        $this->assertNull($added['XE215D']->specifications);
        $this->assertTrue($added->every(fn ($a) => $a->parent_id === null && (bool) $a->eqm_is_active));
        $this->assertCount(1, File::glob($this->storage.'/app/asset-imports/*_added_assets.csv'));
    }

    public function test_existing_codes_long_codes_unknown_types_and_repeats_are_skipped(): void
    {
        $this->artisan('assets:import-aims', ['source' => $this->sample()])
            ->expectsOutputToContain('already in EMMS as MD   01')
            ->expectsOutputToContain('asset code is longer than 30 characters')
            ->expectsOutputToContain('unknown asset type ""')
            ->expectsOutputToContain('listed twice in the file')
            ->assertSuccessful();
    }

    public function test_running_it_again_adds_nothing(): void
    {
        $this->artisan('assets:import-aims', ['source' => $this->sample(), '--execute' => true, '--force' => true])->assertSuccessful();
        $this->artisan('assets:import-aims', ['source' => $this->sample(), '--execute' => true, '--force' => true])
            ->expectsOutputToContain('Nothing to add')
            ->assertSuccessful();

        $this->assertSame(4, DB::table('equipment_units')->count());
    }

    public function test_declining_the_confirmation_adds_nothing(): void
    {
        $this->artisan('assets:import-aims', ['source' => $this->sample(), '--execute' => true])
            ->expectsConfirmation('Add 3 assets to EMMS?', 'no')
            ->assertFailed();

        $this->assertSame(1, DB::table('equipment_units')->count());
    }
}

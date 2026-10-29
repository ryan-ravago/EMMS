<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\AssetMovement;
use App\Models\Equipment;
use App\Services\Aims\AimsMovementImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AimsMovementCsvTest extends AssetMovementTestCase
{
    private string $dir;

    private string $trips;

    private string $users;

    protected function setUp(): void
    {
        parent::setUp();

        (require base_path('database/migrations/2026_10_29_164255_add_aims_log_id_to_asset_movements_table.php'))->up();

        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code');
            $table->string('dep_name')->nullable();
        });
        DB::table('departments')->insert(['dep_id' => 6, 'dep_code' => 'AIMS', 'dep_name' => 'AIMS']);

        $this->dir = sys_get_temp_dir().'/aims-csv-'.uniqid();
        File::ensureDirectoryExists($this->dir);
        $this->trips = $this->dir.'/trips.csv';
        $this->users = $this->dir.'/users.csv';
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    /**
     * Trips as the AIMS import leaves them in the source database.
     *
     * @return array{crane: Equipment, checker: AppUser}
     */
    private function seedSource(): array
    {
        $crane = $this->makeEquipment();
        $truck = $this->makeEquipment();
        $checker = $this->makeUser('Alma', 'Admin');

        $row = fn (int $logId, int $assetId, array $extra = []): array => $extra + [
            'asset_id' => $assetId, 'tag_id' => null, 'out_log_id' => null, 'in_log_id' => null, 'aims_log_id' => $logId,
            'time_out' => '2026-10-01 08:00:00', 'time_in' => '2026-10-01 17:00:00',
            'yard_out_id' => $this->yard->id, 'yard_in_id' => $this->yard->id,
            'manual_out_by' => null, 'manual_out_at' => null, 'manual_out_remarks' => null,
            'manual_in_by' => null, 'manual_in_at' => null, 'manual_in_remarks' => null,
            'acknowledged_by' => null, 'acknowledged_at' => null, 'acknowledgement_remarks' => null,
            'disregarded_by' => null, 'disregarded_at' => null, 'disregard_remarks' => null,
            'created_at' => '2026-10-01 17:00:00', 'updated_at' => '2026-10-01 17:00:00',
        ];

        app(AimsMovementImporter::class)->persist([
            $row(101, $crane->eqm_id, ['acknowledged_by' => $checker->user_id, 'acknowledged_at' => '2026-10-02 09:00:00', 'acknowledgement_remarks' => 'Acknowledged in AIMS', 'manual_in_by' => $checker->user_id, 'manual_in_remarks' => 'Entered manually in AIMS']),
            $row(102, $crane->eqm_id, ['time_out' => null, 'yard_out_id' => null]),
            $row(103, $truck->eqm_id, ['time_in' => null, 'yard_in_id' => null]),
        ]);

        // A trip from real scans must not travel.
        $this->makeTag($truck, 'REAL1');
        AssetMovement::factory()->create(['asset_id' => $truck->eqm_id, 'tag_id' => 'REAL1']);

        return ['crane' => $crane, 'checker' => $checker];
    }

    /**
     * Empties the trips and gives the people new ids, as a different database would have them.
     */
    private function becomeProduction(AppUser $checker, bool $keepPerson): ?int
    {
        DB::table('asset_movements')->delete();
        DB::table('app_users')->where('user_id', $checker->user_id)->delete();

        if (! $keepPerson) {
            return null;
        }

        return AppUser::create(['user_fname' => 'Alma', 'user_lname' => 'Admin', 'user_email' => $checker->user_email, 'user_dep_id' => 6, 'is_active' => true])->user_id + 100;
    }

    private function importArgs(array $extra = []): array
    {
        return ['--file' => $this->trips, '--users-file' => $this->users] + $extra;
    }

    public function test_the_export_names_things_instead_of_pointing_at_ids_and_leaves_real_scan_trips_out(): void
    {
        $seed = $this->seedSource();

        $this->artisan('movements:export-aims-csv', ['--file' => $this->trips, '--users-file' => $this->users])->assertSuccessful();

        $lines = array_map('str_getcsv', file($this->trips, FILE_IGNORE_NEW_LINES));
        $header = array_shift($lines);
        $rows = array_map(fn (array $line): array => array_combine($header, $line), $lines);

        $this->assertCount(3, $rows, 'only the 3 AIMS trips, not the one built from scans');
        $this->assertSame('101', $rows[0]['aims_log_id']);
        $this->assertSame($seed['crane']->eqm_prc_code, $rows[0]['equipment_code']);
        $this->assertSame('Valenzuela Yard', $rows[0]['yard_out']);
        $this->assertSame($seed['checker']->user_email, $rows[0]['acknowledged_by']);
        $this->assertSame('', $rows[1]['time_out']);
        $this->assertSame('', $rows[1]['yard_out']);

        $this->assertStringContainsString($seed['checker']->user_email, file_get_contents($this->users));
        $this->assertStringContainsString('Alma', file_get_contents($this->users));
    }

    public function test_the_preview_writes_nothing_and_the_import_finds_everything_again_by_name(): void
    {
        $seed = $this->seedSource();
        $this->artisan('movements:export-aims-csv', ['--file' => $this->trips, '--users-file' => $this->users])->assertSuccessful();
        $newId = $this->becomeProduction($seed['checker'], keepPerson: true);

        $this->artisan('movements:import-aims-csv', $this->importArgs())->assertSuccessful();
        $this->assertSame(0, AssetMovement::count(), 'the preview adds nothing');

        $this->artisan('movements:import-aims-csv', $this->importArgs(['--execute' => true]))->assertSuccessful();

        $this->assertSame(3, AssetMovement::count());
        $trip = AssetMovement::where('aims_log_id', 101)->sole();
        $this->assertSame($seed['crane']->eqm_id, $trip->asset_id);
        $this->assertSame($this->yard->id, $trip->yard_out_id);
        $this->assertSame(AppUser::where('user_email', $seed['checker']->user_email)->value('user_id'), $trip->acknowledged_by);
        $this->assertSame('acknowledged', $trip->status);
        $this->assertSame('Acknowledged in AIMS', $trip->acknowledgement_remarks);
        $this->assertTrue($trip->isManualIn());
        $this->assertSame('missing_time_out', AssetMovement::where('aims_log_id', 102)->sole()->status);
        $this->assertSame('missing_time_in', AssetMovement::where('aims_log_id', 103)->sole()->status);
        $this->assertNotNull($newId);
    }

    public function test_running_the_import_again_adds_nothing(): void
    {
        $seed = $this->seedSource();
        $this->artisan('movements:export-aims-csv', ['--file' => $this->trips, '--users-file' => $this->users])->assertSuccessful();
        $this->becomeProduction($seed['checker'], keepPerson: true);

        $this->artisan('movements:import-aims-csv', $this->importArgs(['--execute' => true]))->assertSuccessful();
        $this->artisan('movements:import-aims-csv', $this->importArgs(['--execute' => true]))->assertSuccessful();

        $this->assertSame(3, AssetMovement::count());
    }

    public function test_a_person_production_does_not_have_is_left_out_unless_create_users_is_used(): void
    {
        $seed = $this->seedSource();
        $this->artisan('movements:export-aims-csv', ['--file' => $this->trips, '--users-file' => $this->users])->assertSuccessful();
        $this->becomeProduction($seed['checker'], keepPerson: false);

        // Without the flag the trip arrives, but who acknowledged it is not invented.
        $this->artisan('movements:import-aims-csv', $this->importArgs(['--execute' => true]))->assertSuccessful();
        $trip = AssetMovement::where('aims_log_id', 101)->sole();
        $this->assertNull($trip->acknowledged_by);
        $this->assertNull($trip->acknowledged_at);
        $this->assertSame('pending_acknowledgement', $trip->status);

        // With it, the person is created (inactive, AIMS department) and the verification is kept.
        AssetMovement::query()->delete();
        $this->artisan('movements:import-aims-csv', $this->importArgs(['--execute' => true, '--create-users' => true]))->assertSuccessful();

        $person = AppUser::where('user_email', $seed['checker']->user_email)->sole();
        $this->assertFalse((bool) $person->is_active);
        $this->assertSame(6, (int) $person->user_dep_id);
        $this->assertSame('Alma', $person->user_fname);
        $this->assertSame($person->user_id, AssetMovement::where('aims_log_id', 101)->sole()->acknowledged_by);
    }

    public function test_equipment_production_does_not_have_is_skipped_and_named(): void
    {
        $seed = $this->seedSource();
        $this->artisan('movements:export-aims-csv', ['--file' => $this->trips, '--users-file' => $this->users])->assertSuccessful();
        $this->becomeProduction($seed['checker'], keepPerson: true);
        DB::table('equipment_units')->where('eqm_id', $seed['crane']->eqm_id)->update(['eqm_prc_code' => 'RENAMED-IN-PROD']);

        $this->artisan('movements:import-aims-csv', $this->importArgs(['--execute' => true]))
            ->expectsOutputToContain('Equipment codes not found')
            ->assertSuccessful();

        $this->assertSame([103], AssetMovement::pluck('aims_log_id')->all(), 'only the other equipment\'s trip is added');
    }

    public function test_the_rollback_removes_only_the_trips_that_came_from_aims(): void
    {
        $seed = $this->seedSource();
        $realId = AssetMovement::whereNull('aims_log_id')->value('id');
        $this->artisan('movements:export-aims-csv', ['--file' => $this->trips, '--users-file' => $this->users])->assertSuccessful();
        unset($seed);

        $this->artisan('movements:import-aims-csv', ['--rollback' => true])->assertSuccessful();
        $this->assertSame(4, AssetMovement::count(), 'the preview removes nothing');

        $this->artisan('movements:import-aims-csv', ['--rollback' => true, '--execute' => true])->assertSuccessful();
        $this->assertSame([$realId], AssetMovement::pluck('id')->all());
    }

    public function test_a_database_that_has_not_been_migrated_is_told_so(): void
    {
        (require base_path('database/migrations/2026_10_29_164255_add_aims_log_id_to_asset_movements_table.php'))->down();

        $this->artisan('movements:import-aims-csv', $this->importArgs())
            ->expectsOutputToContain('php artisan migrate')
            ->assertFailed();
    }

    public function test_a_file_that_is_missing_columns_is_refused(): void
    {
        file_put_contents($this->trips, "aims_log_id,equipment_code\n1,ABC\n");

        $this->expectException(\InvalidArgumentException::class);

        $this->artisan('movements:import-aims-csv', $this->importArgs());
    }
}

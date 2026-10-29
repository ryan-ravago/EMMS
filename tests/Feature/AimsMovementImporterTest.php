<?php

namespace Tests\Feature;

use App\Models\AssetMovement;
use App\Services\Aims\AimsMovementImporter;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AimsMovementImporterTest extends AssetMovementTestCase
{
    private int $craneId;

    private int $truckId;

    protected function setUp(): void
    {
        parent::setUp();

        (require base_path('database/migrations/2026_10_29_164255_add_aims_log_id_to_asset_movements_table.php'))->up();

        Carbon::setTestNow('2026-10-29 12:00:00');

        $this->craneId = $this->makeEquipment()->eqm_id;
        $this->truckId = $this->makeEquipment()->eqm_id;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function importer(): AimsMovementImporter
    {
        return app(AimsMovementImporter::class);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function log(int $id, int $itemId, ?string $out, ?string $in, array $attributes = []): object
    {
        return (object) ($attributes + [
            'ID' => $id, 'ITEM_ID' => $itemId, 'TIME_OUT' => $out, 'TIME_IN' => $in,
            'LOCATION_OUT' => $out ? 3 : null, 'LOCATION_IN' => $in ? 3 : null,
            'IS_MANUAL_IN' => 0, 'MANUAL_IN_BY' => null, 'IS_MANUAL_OUT' => 0, 'MANUAL_OUT_BY' => null,
            'ACKNOWLEDGED_BY' => null, 'ACKNOWLEDGED_DATE' => null, 'IS_DISREGARD' => 0, 'IS_DELETED' => 0,
            'CREATED_DATE' => $out ?? $in,
        ]);
    }

    /**
     * @param  list<object>  $logs
     * @return array{rows: list<array<string, mixed>>, skipped: list<array<string, mixed>>, unmappedUsers: list<int>}
     */
    private function plan(array $logs, array $userMap = [], array $alreadyImported = [], array $aliases = []): array
    {
        $parents = new Collection([
            (object) ['ID' => 10, 'NAME' => 'CC2800 #2'],
            (object) ['ID' => 11, 'NAME' => 'T 12'],
            (object) ['ID' => 12, 'NAME' => 'GHOST 1'],
            (object) ['ID' => 13, 'NAME' => 'TWIN'],
            (object) ['ID' => 14, 'NAME' => 'SV 1'],
            (object) ['ID' => 15, 'NAME' => 'OLD NAME'],
        ]);

        $units = [
            'cc2800#2' => [['id' => $this->craneId, 'type' => 'EQM']],
            't12' => [['id' => $this->truckId, 'type' => 'EQM']],
            'twin' => [['id' => 901, 'type' => 'EQM'], ['id' => 902, 'type' => 'EQM']],
            'sv1' => [['id' => 903, 'type' => 'SRVV']],
        ];

        return $this->importer()->plan(
            new Collection($logs),
            $parents,
            new Collection([(object) ['ID' => 500]]),
            $units,
            $aliases,
            $userMap,
            [3 => $this->yard->id],
            $alreadyImported,
        );
    }

    private function reasons(array $plan): array
    {
        return collect($plan['skipped'])->pluck('reason', 'aims_id')->all();
    }

    public function test_a_complete_trip_keeps_its_times_yards_and_who_checked_it(): void
    {
        $checker = $this->makeUser('Alma', 'Admin');
        $plan = $this->plan([
            $this->log(1, 10, '2026-10-01 08:00:00', '2026-10-01 17:00:00', ['ACKNOWLEDGED_BY' => 7, 'ACKNOWLEDGED_DATE' => '2026-10-02 09:00:00', 'IS_MANUAL_IN' => 1, 'MANUAL_IN_BY' => 7]),
        ], userMap: [7 => $checker->user_id]);

        $this->assertCount(1, $plan['rows']);
        $row = $plan['rows'][0];
        $this->assertSame($this->craneId, $row['asset_id']);
        $this->assertSame(1, $row['aims_log_id']);
        $this->assertSame('2026-10-01 08:00:00', $row['time_out']);
        $this->assertSame('2026-10-01 17:00:00', $row['time_in']);
        $this->assertSame($this->yard->id, $row['yard_out_id']);
        $this->assertSame($this->yard->id, $row['yard_in_id']);
        $this->assertNull($row['tag_id']);
        $this->assertNull($row['out_log_id']);
        $this->assertSame($checker->user_id, $row['acknowledged_by']);
        $this->assertSame('2026-10-02 09:00:00', $row['acknowledged_at']);
        $this->assertSame($checker->user_id, $row['manual_in_by']);
        $this->assertNull($row['manual_out_by']);
    }

    public function test_an_unacknowledged_trip_arrives_pending_and_a_time_in_only_trip_is_kept(): void
    {
        $plan = $this->plan([
            $this->log(1, 10, '2026-10-01 08:00:00', '2026-10-01 17:00:00'),
            $this->log(2, 11, null, '2026-10-03 09:00:00'),
        ]);

        $this->importer()->persist($plan['rows']);

        $this->assertSame(AssetMovement::STATUS_PENDING_ACKNOWLEDGEMENT, AssetMovement::where('aims_log_id', 1)->sole()->status);
        $this->assertSame(AssetMovement::STATUS_MISSING_TIME_OUT, AssetMovement::where('aims_log_id', 2)->sole()->status);
        $this->assertNull(AssetMovement::where('aims_log_id', 2)->sole()->yard_out_id);
    }

    public function test_an_acknowledgement_on_a_trip_that_never_came_back_is_not_carried_over(): void
    {
        $plan = $this->plan([
            $this->log(1, 10, '2026-10-01 08:00:00', null, ['ACKNOWLEDGED_BY' => 7, 'ACKNOWLEDGED_DATE' => '2026-10-02 09:00:00']),
        ], userMap: [7 => $this->makeUser()->user_id]);

        $this->assertNull($plan['rows'][0]['acknowledged_by']);
        $this->assertNull($plan['rows'][0]['acknowledged_at']);
    }

    public function test_logs_that_cannot_become_equipment_trips_are_skipped_with_a_reason(): void
    {
        $plan = $this->plan([
            $this->log(1, 500, '2026-10-01 08:00:00', '2026-10-01 17:00:00'),   // an accessory
            $this->log(2, 999, '2026-10-01 08:00:00', '2026-10-01 17:00:00'),   // nothing in AIMS
            $this->log(3, 12, '2026-10-01 08:00:00', '2026-10-01 17:00:00'),    // no EMMS unit
            $this->log(4, 13, '2026-10-01 08:00:00', '2026-10-01 17:00:00'),    // two EMMS units
            $this->log(5, 14, '2026-10-01 08:00:00', '2026-10-01 17:00:00'),    // not equipment
            $this->log(6, 10, '2026-10-01 17:00:00', '2026-10-01 08:00:00'),    // back before it left
            $this->log(7, 10, '2026-10-01 08:00:00', '2026-12-25 17:00:00'),    // in the future
            $this->log(8, 10, null, null),                                      // no times
            $this->log(9, 10, '2026-10-01 08:00:00', '2026-10-01 17:00:00', ['IS_DELETED' => 1]),
            $this->log(10, 10, '2026-10-05 08:00:00', '2026-10-05 17:00:00'),
        ], alreadyImported: [10 => true]);

        $this->assertSame([], $plan['rows']);
        $this->assertSame([
            1 => AimsMovementImporter::SKIP_ACCESSORY,
            2 => AimsMovementImporter::SKIP_NO_ASSET,
            3 => AimsMovementImporter::SKIP_NO_UNIT,
            4 => AimsMovementImporter::SKIP_AMBIGUOUS,
            5 => AimsMovementImporter::SKIP_NOT_EQUIPMENT,
            6 => AimsMovementImporter::SKIP_BAD_TIMES,
            7 => AimsMovementImporter::SKIP_FUTURE,
            8 => AimsMovementImporter::SKIP_NO_TIMES,
            9 => AimsMovementImporter::SKIP_DELETED,
            10 => AimsMovementImporter::SKIP_ALREADY_IMPORTED,
        ], $this->reasons($plan));
    }

    public function test_an_aliased_aims_name_finds_its_renamed_equipment(): void
    {
        $plan = $this->plan([$this->log(1, 15, '2026-10-01 08:00:00', '2026-10-01 17:00:00')], aliases: ['OLD NAME' => 'T 12']);

        $this->assertCount(1, $plan['rows']);
        $this->assertSame($this->truckId, $plan['rows'][0]['asset_id']);
    }

    public function test_only_the_latest_never_closed_trip_of_each_equipment_is_kept(): void
    {
        $plan = $this->plan([
            // The crane went out three times and never came back: only the last one is real.
            $this->log(1, 10, '2026-09-01 08:00:00', null),
            $this->log(2, 10, '2026-09-10 08:00:00', null),
            $this->log(3, 10, '2026-09-20 08:00:00', null),
            // The truck went out, then made a complete trip later: the open one is stale.
            $this->log(4, 11, '2026-09-01 08:00:00', null),
            $this->log(5, 11, '2026-09-15 08:00:00', '2026-09-15 17:00:00'),
        ]);

        $this->assertEqualsCanonicalizing([3, 5], collect($plan['rows'])->pluck('aims_log_id')->all());
        $this->assertSame(AimsMovementImporter::SKIP_STALE_OPEN, $this->reasons($plan)[1]);
        $this->assertSame(AimsMovementImporter::SKIP_STALE_OPEN, $this->reasons($plan)[2]);
        $this->assertSame(AimsMovementImporter::SKIP_STALE_OPEN, $this->reasons($plan)[4]);
    }

    public function test_an_open_trip_after_complete_ones_is_kept(): void
    {
        $plan = $this->plan([
            $this->log(1, 10, '2026-09-01 08:00:00', '2026-09-01 17:00:00'),
            $this->log(2, 10, '2026-09-10 08:00:00', null),
        ]);

        $this->assertEqualsCanonicalizing([1, 2], collect($plan['rows'])->pluck('aims_log_id')->all());
    }

    public function test_a_user_who_cannot_be_matched_is_reported_and_the_flag_is_not_invented(): void
    {
        $plan = $this->plan([
            $this->log(1, 10, '2026-10-01 08:00:00', '2026-10-01 17:00:00', ['IS_MANUAL_OUT' => 1, 'MANUAL_OUT_BY' => 42, 'ACKNOWLEDGED_BY' => 42, 'ACKNOWLEDGED_DATE' => '2026-10-02 09:00:00']),
        ]);

        $this->assertSame([42], $plan['unmappedUsers']);
        $this->assertNull($plan['rows'][0]['manual_out_by']);
        $this->assertNull($plan['rows'][0]['acknowledged_by']);
    }

    public function test_importing_twice_adds_nothing_and_the_rollback_removes_only_the_aims_trips(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'REAL1');
        $real = AssetMovement::factory()->create(['asset_id' => $this->craneId, 'tag_id' => 'REAL1']);
        $plan = $this->plan([
            $this->log(1, 10, '2026-10-01 08:00:00', '2026-10-01 17:00:00'),
            $this->log(2, 11, '2026-10-02 08:00:00', '2026-10-02 17:00:00'),
        ]);

        $this->assertSame(2, $this->importer()->persist($plan['rows']));
        $this->assertSame(0, $this->importer()->persist($plan['rows']), 'a second run inserts nothing');
        $this->assertSame(3, AssetMovement::count());

        $again = $this->plan([$this->log(1, 10, '2026-10-01 08:00:00', '2026-10-01 17:00:00')], alreadyImported: AssetMovement::whereNotNull('aims_log_id')->pluck('aims_log_id')->mapWithKeys(fn ($id) => [(int) $id => true])->all());
        $this->assertSame([], $again['rows']);

        $this->assertSame(2, $this->importer()->rollback());
        $this->assertSame([$real->id], AssetMovement::pluck('id')->all(), 'the trip from real scans is untouched');
    }

    public function test_the_cached_trip_counts_follow_the_import_and_the_rollback(): void
    {
        $this->assertSame(0, AssetMovement::statusCounts()['pending']);

        $this->importer()->persist($this->plan([$this->log(1, 10, '2026-10-01 08:00:00', '2026-10-01 17:00:00')])['rows']);
        $this->assertSame(1, AssetMovement::statusCounts()['pending']);

        $this->importer()->rollback();
        $this->assertSame(0, AssetMovement::statusCounts()['pending']);
    }
}

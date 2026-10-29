<?php

namespace Tests\Feature;

use App\Models\AssetMovement;
use App\Services\Rfid\MovementRecorder;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;

class AssetMovementRecorderTest extends AssetMovementTestCase
{
    private function recorder(): MovementRecorder
    {
        return app(MovementRecorder::class);
    }

    public function test_an_out_scan_followed_by_an_in_scan_is_one_trip(): void
    {
        $tag = $this->makeTag($equipment = $this->makeEquipment(), 'AAA1');

        $out = $this->scan($tag, 'OUT', '2026-10-20 08:00:00');
        $in = $this->scan($tag, 'IN', '2026-10-20 17:30:00');

        $result = $this->recorder()->record([$out, $in]);

        $this->assertSame(['opened' => 1, 'closed' => 1, 'in_only' => 0, 'ignored' => 0, 'skipped' => 0], $result);

        $trip = AssetMovement::sole();
        $this->assertSame($equipment->eqm_id, $trip->asset_id);
        $this->assertSame('AAA1', $trip->tag_id);
        $this->assertSame('2026-10-20 08:00:00', $trip->time_out->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-20 17:30:00', $trip->time_in->format('Y-m-d H:i:s'));
        $this->assertSame($out->id, $trip->out_log_id);
        $this->assertSame($in->id, $trip->in_log_id);
        $this->assertSame($this->yard->id, $trip->yard_out_id);
        $this->assertSame($this->yard->id, $trip->yard_in_id);
        $this->assertSame(AssetMovement::STATUS_PENDING_ACKNOWLEDGEMENT, $trip->status);
    }

    public function test_an_out_scan_without_an_in_scan_stays_open(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-10-20 08:00:00')]);

        $trip = AssetMovement::sole();
        $this->assertNull($trip->time_in);
        $this->assertSame(AssetMovement::STATUS_MISSING_TIME_IN, $trip->status);
    }

    public function test_an_in_scan_without_an_open_trip_makes_a_trip_that_is_missing_its_time_out(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $result = $this->recorder()->record([$this->scan($tag, 'IN', '2026-10-20 17:30:00')]);

        $this->assertSame(1, $result['in_only']);

        $trip = AssetMovement::sole();
        $this->assertNull($trip->time_out);
        $this->assertSame(AssetMovement::STATUS_MISSING_TIME_OUT, $trip->status);
    }

    public function test_scans_of_other_tags_of_the_same_equipment_belong_to_the_same_trip(): void
    {
        $equipment = $this->makeEquipment();
        $first = $this->makeTag($equipment, 'AAA1');
        $second = $this->makeTag($equipment, 'AAA2');

        $result = $this->recorder()->record([
            $this->scan($first, 'OUT', '2026-10-20 08:00:00'),
            $this->scan($second, 'OUT', '2026-10-20 08:00:06'),
            $this->scan($first, 'IN', '2026-10-20 17:30:00'),
            $this->scan($second, 'IN', '2026-10-20 17:30:09'),
        ]);

        $this->assertSame(['opened' => 1, 'closed' => 1, 'in_only' => 0, 'ignored' => 2, 'skipped' => 0], $result);
        $this->assertSame(1, AssetMovement::count());
        $this->assertSame('AAA1', AssetMovement::sole()->tag_id);
    }

    public function test_scans_further_apart_than_the_window_are_separate_trips(): void
    {
        config(['rfid.movement_window_seconds' => 300]);
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([
            $this->scan($tag, 'OUT', '2026-10-20 08:00:00'),
            $this->scan($tag, 'OUT', '2026-10-20 08:06:00'),
        ]);

        $this->assertSame(2, AssetMovement::count());
    }

    public function test_an_in_scan_closes_the_latest_open_trip_and_leaves_older_ones_open(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([
            $this->scan($tag, 'OUT', '2026-10-18 08:00:00'),
            $this->scan($tag, 'OUT', '2026-10-19 08:00:00'),
            $this->scan($tag, 'IN', '2026-10-19 17:00:00'),
        ]);

        $trips = AssetMovement::orderBy('time_out')->get();
        $this->assertNull($trips[0]->time_in);
        $this->assertSame('2026-10-19 17:00:00', $trips[1]->time_in->format('Y-m-d H:i:s'));
    }

    public function test_scans_of_accessories_are_ignored(): void
    {
        $tag = $this->makeTag($this->makeAccessory(), 'ACC1');

        $result = $this->recorder()->record([$this->scan($tag, 'OUT', '2026-10-20 08:00:00')]);

        $this->assertSame(1, $result['skipped']);
        $this->assertSame(0, AssetMovement::count());
    }

    public function test_recording_the_same_scans_again_changes_nothing(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $logs = [$this->scan($tag, 'OUT', '2026-10-20 08:00:00'), $this->scan($tag, 'IN', '2026-10-20 17:30:00')];

        $this->recorder()->record($logs);
        $again = $this->recorder()->record($logs);

        $this->assertSame(2, $again['skipped']);
        $this->assertSame(1, AssetMovement::count());
    }

    public function test_scans_handed_over_out_of_order_are_paired_by_time(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $in = $this->scan($tag, 'IN', '2026-10-20 17:30:00');
        $out = $this->scan($tag, 'OUT', '2026-10-20 08:00:00');

        $this->recorder()->record([$in, $out]);

        $trip = AssetMovement::sole();
        $this->assertSame(AssetMovement::STATUS_PENDING_ACKNOWLEDGEMENT, $trip->status);
    }

    public function test_a_disregarded_trip_is_not_closed_by_a_later_in_scan(): void
    {
        $tag = $this->makeTag($equipment = $this->makeEquipment(), 'AAA1');
        $user = $this->makeUser();

        AssetMovement::factory()->missingTimeIn()->disregarded($user->user_id)->create([
            'asset_id' => $equipment->eqm_id,
            'tag_id' => 'AAA1',
            'time_out' => '2026-10-20 08:00:00',
        ]);

        $this->recorder()->record([$this->scan($tag, 'IN', '2026-10-20 17:30:00')]);

        $this->assertSame(1, AssetMovement::missingTimeOut()->count());
    }

    public function test_the_reader_api_builds_trips_from_the_scans_it_stores(): void
    {
        $this->makeTag($equipment = $this->makeEquipment(), 'AAA1');
        $this->makeTag($equipment, 'AAA2');
        $this->makeTag($this->makeAccessory(), 'ACC1');
        Sanctum::actingAs($this->makeUser(), ['rfid:write']);

        $this->postJson("/v1/{$this->yard->id}/rfid-logs", [
            ['Tag' => 'aaa1', 'Status' => 'out', 'Timestamp' => '2026-10-20 08:00:00'],
            ['Tag' => 'AAA2', 'Status' => 'OUT', 'Timestamp' => '2026-10-20 08:00:05'],
            ['Tag' => 'ACC1', 'Status' => 'OUT', 'Timestamp' => '2026-10-20 08:00:05'],
            ['Tag' => 'AAA1', 'Status' => 'IN', 'Timestamp' => '2026-10-20 17:30:00'],
        ])->assertOk()->assertJsonPath('inserted', 4);

        $trip = AssetMovement::sole();
        $this->assertSame($equipment->eqm_id, $trip->asset_id);
        $this->assertSame(AssetMovement::STATUS_PENDING_ACKNOWLEDGEMENT, $trip->status);

        // Posting the same batch again stores nothing and creates no extra trip.
        $this->postJson("/v1/{$this->yard->id}/rfid-logs", [
            ['Tag' => 'aaa1', 'Status' => 'out', 'Timestamp' => '2026-10-20 08:00:00'],
        ])->assertOk()->assertJsonPath('inserted', 0);

        $this->assertSame(1, AssetMovement::count());
    }

    public function test_a_failure_while_building_trips_does_not_fail_the_reader_request(): void
    {
        $this->makeTag($this->makeEquipment(), 'AAA1');
        Sanctum::actingAs($this->makeUser(), ['rfid:write']);
        Schema::drop('asset_movements');

        $this->postJson("/v1/{$this->yard->id}/rfid-logs", [
            ['Tag' => 'AAA1', 'Status' => 'OUT', 'Timestamp' => '2026-10-20 08:00:00'],
        ])->assertOk()->assertJsonPath('inserted', 1);
    }
}

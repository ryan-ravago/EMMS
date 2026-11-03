<?php

namespace Tests\Feature;

use App\Jobs\ProcessRfidRawLogs;
use App\Models\AssetMovement;
use App\Models\AssetTagLog;
use App\Models\RfidRawLog;
use App\Services\Rfid\RawLogProcessor;
use App\Services\Rfid\RfidHealthCheck;
use App\Services\Rfid\RfidHealthReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;

class RfidRawLogTest extends AssetMovementTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-03 10:00:00');
        config(['rfid.raw_log_retention_days' => 14, 'rfid.process_chunk' => 1000, 'rfid.stuck_after_minutes' => 15]);
    }

    private function url(): string
    {
        return "/v1/{$this->yard->id}/rfid-logs";
    }

    private function asReader(array $abilities = ['rfid:write']): void
    {
        Sanctum::actingAs($this->makeUser(), $abilities);
    }

    /**
     * A raw read as the API would have stored it.
     *
     * @param  array<string, mixed>  $extra
     */
    private function raw(string $tag, string $status, string $detectedAt, array $extra = []): int
    {
        return DB::table('rfid_raw_logs')->insertGetId($extra + [
            'batch_uuid' => 'batch-1',
            'yard_id' => $this->yard->id,
            'tag_id' => $tag,
            'status' => $status,
            'detected_at' => $detectedAt,
        ]);
    }

    private function outcome(int $id): string
    {
        return DB::table('rfid_raw_logs')->where('id', $id)->value('outcome');
    }

    private function process(): array
    {
        return app(RawLogProcessor::class)->run();
    }

    // ---- storing what the reader sends -------------------------------------------------------------------

    public function test_every_read_is_stored_whether_or_not_the_tag_is_known(): void
    {
        Queue::fake();
        $this->makeTag($this->makeEquipment(), 'AAA1');
        $this->asReader();

        $this->postJson($this->url(), [
            ['Tag' => ' aaa1 ', 'Status' => 'out', 'Timestamp' => '2026-11-03 08:00:00'],
            ['Tag' => 'STRANGER', 'Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:01'],
        ])->assertOk()->assertExactJson(['received' => 2, 'inserted' => 2, 'not_inserted' => []]);

        $rows = RfidRawLog::orderBy('id')->get();
        $this->assertSame(['aaa1', 'STRANGER'], $rows->pluck('tag_id')->all());
        $this->assertSame(['OUT', 'IN'], $rows->pluck('status')->all());
        $this->assertSame(['pending', 'pending'], $rows->pluck('outcome')->all());
        $this->assertCount(1, $rows->pluck('batch_uuid')->unique(), 'one batch id per request');
        $this->assertSame('127.0.0.1', $rows[0]->ip);
        $this->assertSame(0, AssetTagLog::count(), 'nothing is moved on while the request is answered');
        Queue::assertPushed(ProcessRfidRawLogs::class);
    }

    public function test_the_same_read_sent_again_is_not_stored_twice(): void
    {
        Queue::fake();
        $this->asReader();
        $body = [['Tag' => 'AAA1', 'Status' => 'OUT', 'Timestamp' => '2026-11-03 08:00:00']];

        $this->postJson($this->url(), $body)->assertOk()->assertJsonPath('inserted', 1);
        $this->postJson($this->url(), $body)->assertOk()->assertJsonPath('inserted', 0);

        $this->assertSame(1, RfidRawLog::count());
        Queue::assertPushed(ProcessRfidRawLogs::class, 1);
    }

    public function test_malformed_requests_are_refused_and_store_nothing(): void
    {
        Queue::fake();
        $this->asReader();

        foreach ([
            [],
            [['Tag' => 'AAA1', 'Status' => 'sideways', 'Timestamp' => '2026-11-03 08:00:00']],
            [['Tag' => 'AAA1', 'Status' => 'IN', 'Timestamp' => '03/11/2026']],
            [['Tag' => str_repeat('a', 65), 'Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:00']],
            [['Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:00']],
            ['not' => 'a list'],
        ] as $body) {
            $this->postJson($this->url(), $body)->assertUnprocessable();
        }

        $this->assertSame(0, RfidRawLog::count());
    }

    public function test_a_batch_with_one_bad_read_is_refused_as_a_whole(): void
    {
        $this->asReader();

        $this->postJson($this->url(), [
            ['Tag' => 'AAA1', 'Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:00'],
            ['Tag' => 'AAA2', 'Status' => 'nope', 'Timestamp' => '2026-11-03 08:00:00'],
        ])->assertUnprocessable();

        $this->assertSame(0, RfidRawLog::count());
    }

    public function test_an_oversized_body_a_missing_yard_and_missing_credentials_are_refused(): void
    {
        $this->postJson($this->url(), [['Tag' => 'AAA1', 'Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:00']])->assertUnauthorized();

        $this->asReader(['something:else']);
        $this->postJson($this->url(), [['Tag' => 'AAA1', 'Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:00']])->assertForbidden();

        $this->asReader();
        $this->postJson($this->url(), [['Tag' => str_repeat('a', 300000), 'Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:00']])->assertStatus(413);
        $this->postJson('/v1/9999/rfid-logs', [['Tag' => 'AAA1', 'Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:00']])->assertNotFound();

        $this->assertSame(0, RfidRawLog::count());
    }

    public function test_a_tag_with_odd_characters_is_stored_safely_and_never_becomes_a_scan(): void
    {
        $this->asReader();

        $this->postJson($this->url(), [['Tag' => "x'; DROP TABLE asset_tags;--", 'Status' => 'IN', 'Timestamp' => '2026-11-03 08:00:00']])->assertOk();

        $this->assertSame(1, RfidRawLog::where('outcome', RfidRawLog::UNKNOWN_TAG)->count(), 'processed right away (sync queue) and not trusted');
        $this->assertTrue(Schema::hasTable('asset_tags'));
        $this->assertSame(0, AssetTagLog::count());
    }

    public function test_the_whole_path_from_the_reader_to_a_trip_works_with_the_queue(): void
    {
        $this->makeTag($equipment = $this->makeEquipment(), 'AAA1');
        $this->asReader();

        $this->postJson($this->url(), [
            ['Tag' => 'aaa1', 'Status' => 'out', 'Timestamp' => '2026-11-03 08:00:00'],
            ['Tag' => 'AAA1', 'Status' => 'IN', 'Timestamp' => '2026-11-03 09:30:00'],
            ['Tag' => 'STRANGER', 'Status' => 'IN', 'Timestamp' => '2026-11-03 09:31:00'],
        ])->assertOk()->assertJsonPath('inserted', 3);

        $this->assertSame(2, AssetTagLog::count());
        $this->assertSame($equipment->eqm_id, AssetMovement::sole()->asset_id);
        $this->assertSame(['processed' => 2, 'unknown_tag' => 1], RfidRawLog::query()->selectRaw('outcome, count(*) c')->groupBy('outcome')->pluck('c', 'outcome')->all());
    }

    // ---- moving the reads of registered tags on ------------------------------------------------------------

    public function test_a_known_tag_becomes_a_scan_and_a_trip_and_an_unknown_tag_creates_nothing(): void
    {
        $this->makeTag($equipment = $this->makeEquipment(), 'AAA1');
        $out = $this->raw('AAA1', 'OUT', '2026-11-03 08:00:00');
        $unknown = $this->raw('NOPE', 'OUT', '2026-11-03 08:00:00');
        DB::table('rfid_raw_logs')->where('id', $out)->update(['received_at' => '2026-11-03 08:00:09']);

        $totals = $this->process();

        $this->assertSame(1, $totals['processed']);
        $this->assertSame(1, $totals['unknown_tag']);
        $this->assertSame(RfidRawLog::PROCESSED, $this->outcome($out));
        $this->assertSame(RfidRawLog::UNKNOWN_TAG, $this->outcome($unknown));
        $this->assertNotNull(DB::table('rfid_raw_logs')->where('id', $out)->value('processed_at'));

        $log = AssetTagLog::sole();
        $this->assertSame('AAA1', $log->tag_id);
        $this->assertSame('2026-11-03 08:00:09', $log->received_at->format('Y-m-d H:i:s'), 'the time the reader server was heard from is kept');
        $this->assertSame($equipment->eqm_id, AssetMovement::sole()->asset_id);
    }

    public function test_tags_are_matched_ignoring_case_and_stored_with_their_registered_spelling(): void
    {
        $this->makeTag($this->makeEquipment(), 'E28069AB');
        $this->raw('e28069ab', 'IN', '2026-11-03 08:00:00');

        $this->process();

        $this->assertSame('E28069AB', AssetTagLog::sole()->tag_id);
    }

    public function test_a_read_that_is_already_a_scan_is_a_duplicate(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $this->scan($tag, 'OUT', '2026-11-03 08:00:00');
        $id = $this->raw('AAA1', 'OUT', '2026-11-03 08:00:00');

        $totals = $this->process();

        $this->assertSame(1, $totals['duplicate']);
        $this->assertSame(RfidRawLog::DUPLICATE, $this->outcome($id));
        $this->assertSame(1, AssetTagLog::count());
    }

    public function test_reads_with_an_impossible_time_are_not_turned_into_scans(): void
    {
        $this->makeTag($this->makeEquipment(), 'AAA1');
        $future = $this->raw('AAA1', 'OUT', '2026-11-03 10:30:00');
        $tooOld = $this->raw('AAA1', 'IN', '2026-10-01 08:00:00');
        $drift = $this->raw('AAA1', 'IN', '2026-11-03 10:04:00'); // within the clock-drift tolerance

        $totals = $this->process();

        $this->assertSame(2, $totals['invalid_time']);
        $this->assertSame(RfidRawLog::INVALID_TIME, $this->outcome($future));
        $this->assertSame(RfidRawLog::INVALID_TIME, $this->outcome($tooOld));
        $this->assertSame(RfidRawLog::PROCESSED, $this->outcome($drift));
        $this->assertSame(1, AssetTagLog::count());
    }

    public function test_processing_again_does_nothing_and_large_backlogs_are_handled_in_chunks(): void
    {
        config(['rfid.process_chunk' => 2]);
        $this->makeTag($this->makeEquipment(), 'AAA1');

        foreach (range(1, 5) as $minute) {
            $this->raw('AAA1', 'IN', "2026-11-03 08:0{$minute}:00");
        }

        $this->assertSame(5, $this->process()['processed']);
        $this->assertSame(5, AssetTagLog::count());
        $this->assertSame(0, array_sum($this->process()));
    }

    public function test_a_failure_while_building_trips_keeps_the_scans(): void
    {
        $this->makeTag($this->makeEquipment(), 'AAA1');
        $id = $this->raw('AAA1', 'OUT', '2026-11-03 08:00:00');
        Schema::drop('asset_movements');

        $this->process();

        $this->assertSame(1, AssetTagLog::count());
        $this->assertSame(RfidRawLog::PROCESSED, $this->outcome($id));
    }

    public function test_a_chunk_that_fails_stays_pending_and_is_parked_after_repeated_failures(): void
    {
        $this->makeTag($this->makeEquipment(), 'AAA1');
        $id = $this->raw('AAA1', 'OUT', '2026-11-03 08:00:00');
        Schema::drop('asset_tag_logs');

        foreach (range(1, RawLogProcessor::MAX_ATTEMPTS - 1) as $attempt) {
            $this->process();
            $this->assertSame(RfidRawLog::PENDING, $this->outcome($id));
            $this->assertSame($attempt, (int) DB::table('rfid_raw_logs')->where('id', $id)->value('attempts'));
        }

        $this->process();
        $this->assertSame(RfidRawLog::FAILED, $this->outcome($id));
    }

    public function test_two_runs_do_not_process_at_the_same_time(): void
    {
        $this->makeTag($this->makeEquipment(), 'AAA1');
        $id = $this->raw('AAA1', 'OUT', '2026-11-03 08:00:00');

        $lock = Cache::lock('rfid-raw-log-processing', 60);
        $this->assertTrue($lock->get());

        $this->assertSame(0, array_sum($this->process()));
        $this->assertSame(RfidRawLog::PENDING, $this->outcome($id));

        $lock->release();
        $this->assertSame(1, $this->process()['processed']);
    }

    public function test_the_command_processes_the_waiting_reads(): void
    {
        $this->makeTag($this->makeEquipment(), 'AAA1');
        $id = $this->raw('AAA1', 'OUT', '2026-11-03 08:00:00');

        $this->artisan('rfid:process-raw-logs')->assertSuccessful();

        $this->assertSame(RfidRawLog::PROCESSED, $this->outcome($id));
    }

    // ---- retention ---------------------------------------------------------------------------------------

    public function test_raw_reads_older_than_two_weeks_are_deleted_whatever_happened_to_them(): void
    {
        $old = fn (string $outcome): int => $this->raw('T'.$outcome, 'IN', '2026-10-01 08:00:00', ['outcome' => $outcome, 'received_at' => '2026-10-19 09:59:00']);
        $oldIds = array_map($old, ['pending', 'processed', 'unknown_tag', 'failed']);
        $kept = $this->raw('TNEW', 'IN', '2026-10-30 08:00:00', ['received_at' => '2026-10-20 10:01:00']);

        $this->artisan('model:prune', ['--model' => [RfidRawLog::class]])->assertSuccessful();

        $this->assertSame(0, DB::table('rfid_raw_logs')->whereIn('id', $oldIds)->count());
        $this->assertSame([$kept], DB::table('rfid_raw_logs')->pluck('id')->all());
    }

    public function test_the_retention_period_follows_the_setting(): void
    {
        config(['rfid.raw_log_retention_days' => 3]);
        $this->raw('TOLD', 'IN', '2026-10-30 08:00:00', ['received_at' => '2026-10-30 09:00:00']);
        $this->raw('TNEW', 'IN', '2026-11-02 08:00:00', ['received_at' => '2026-11-02 09:00:00']);

        $this->artisan('model:prune', ['--model' => [RfidRawLog::class]])->assertSuccessful();

        $this->assertSame(['TNEW'], DB::table('rfid_raw_logs')->pluck('tag_id')->all());
    }

    // ---- health -------------------------------------------------------------------------------------------

    public function test_reads_that_wait_too_long_are_reported_as_stuck_and_clear_once_processed(): void
    {
        $this->makeTag($this->makeEquipment(), 'AAA1');
        $service = $this->makeUser('RFID', 'Integration');
        $service->createToken(config('rfid.token_name'), ['rfid:write']);
        $id = $this->raw('AAA1', 'OUT', '2026-11-03 08:00:00');

        DB::table('rfid_raw_logs')->where('id', $id)->update(['received_at' => DB::raw("datetime('now', '-20 minutes')")]);
        $this->assertSame(RfidHealthReport::CAUSE_STUCK, app(RfidHealthCheck::class)->inspect()->cause);

        $this->process();
        $this->assertNotSame(RfidHealthReport::CAUSE_STUCK, app(RfidHealthCheck::class)->inspect()->cause);
    }

    public function test_recent_raw_reads_count_as_the_reader_server_being_in_contact(): void
    {
        $service = $this->makeUser('RFID', 'Integration');
        $service->createToken(config('rfid.token_name'), ['rfid:write']);

        $this->assertSame(RfidHealthReport::CAUSE_NO_CONTACT, app(RfidHealthCheck::class)->inspect()->cause, 'a token nobody used');

        $this->raw('STRANGER', 'IN', '2026-11-03 09:55:00', ['outcome' => RfidRawLog::UNKNOWN_TAG, 'received_at' => DB::raw("datetime('now', '-3 minutes')")]);

        $report = app(RfidHealthCheck::class)->inspect();
        $this->assertSame(RfidHealthReport::CAUSE_NO_SCANS, $report->cause, 'in contact, but no known tag stored');
        $this->assertNotNull($report->lastRequestAt);
    }
}

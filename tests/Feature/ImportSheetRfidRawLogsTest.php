<?php

namespace Tests\Feature;

use App\Models\AssetMovement;
use App\Models\Equipment;
use App\Models\RfidRawLog;
use App\Services\Rfid\RawLogProcessor;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class ImportSheetRfidRawLogsTest extends AssetMovementTestCase
{
    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-11 12:00:00');
        $this->file = tempnam(sys_get_temp_dir(), 'sheet');
    }

    protected function tearDown(): void
    {
        @unlink($this->file);

        parent::tearDown();
    }

    private function run_(array $lines, bool $execute = true, array $options = []): void
    {
        file_put_contents($this->file, "ITEM_NAME,TIME_OUT,TIME_IN\n".implode("\n", $lines)."\n");

        $this->artisan('rfid:import-sheet-logs', array_filter([
            '--file' => $this->file,
            '--aliases' => 'missing.csv',
            '--yard' => $this->yard->id,
            '--execute' => $execute,
        ] + $options))->assertSuccessful();
    }

    public function test_out_and_in_times_become_raw_logs_that_build_the_trip(): void
    {
        $asset = Equipment::factory()->equipment()->create(['eqm_prc_code' => 'TH 29']);
        $this->makeTag($asset, 'TAG-1');

        $this->run_(['TH29,2026-11-09 17:17:00,2026-11-10 0:36:00']);

        $this->assertSame(
            ['OUT|2026-11-09 17:17:00', 'IN|2026-11-10 00:36:00'],
            RfidRawLog::query()->orderBy('detected_at')->get()->map(fn (RfidRawLog $log): string => $log->status.'|'.$log->detected_at->format('Y-m-d H:i:s'))->all(),
        );
        $this->assertSame(2, RfidRawLog::query()->where(['tag_id' => 'TAG-1', 'outcome' => RfidRawLog::PROCESSED, 'yard_id' => $this->yard->id])->count());
        $this->assertSame(1, AssetMovement::query()->where('asset_id', $asset->eqm_id)->whereNotNull('time_out')->whereNotNull('time_in')->count());
    }

    public function test_dashes_unknown_names_and_old_times_add_nothing_and_a_rerun_adds_no_duplicates(): void
    {
        $asset = Equipment::factory()->equipment()->create(['eqm_prc_code' => 'TH30']);
        $this->makeTag($asset, 'TAG-2');

        $lines = ['TH30,2026-11-10 0:30:00,-', 'NOPE,2026-11-10 0:30:00,-', 'TH30,2026-10-01 8:00:00,-'];

        $this->run_($lines);
        $this->run_($lines);

        $this->assertSame(1, RfidRawLog::query()->count());
    }

    public function test_preview_writes_nothing(): void
    {
        $asset = Equipment::factory()->equipment()->create(['eqm_prc_code' => 'TH30']);
        $this->makeTag($asset, 'TAG-2');

        $this->run_(['TH30,2026-11-10 0:30:00,-'], execute: false);

        $this->assertSame(0, RfidRawLog::query()->count());
    }

    public function test_reads_the_production_already_has_are_not_added_again(): void
    {
        $asset = Equipment::factory()->equipment()->create(['eqm_prc_code' => 'TH30']);
        $tag = $this->makeTag($asset, 'TAG-2');
        $this->makeTag($asset, 'TAG-3');
        $this->scan($tag, 'OUT', '2026-11-10 00:30:10');
        AssetMovement::query()->create(['asset_id' => $asset->eqm_id, 'tag_id' => 'TAG-2', 'time_out' => '2026-11-09 10:57:00', 'time_in' => '2026-11-09 22:52:00']);

        $this->run_(['TH30,2026-11-10 0:30:00,-', 'TH30,2026-11-09 10:57:00,2026-11-09 22:52:00', 'TH30,2026-11-10 5:00:00,-']);

        $this->assertSame(['2026-11-10 05:00:00'], RfidRawLog::query()->get()->map(fn (RfidRawLog $log): string => $log->detected_at->format('Y-m-d H:i:s'))->all());
    }

    public function test_the_yard_can_be_given_by_name_and_other_column_names_can_be_used(): void
    {
        Equipment::factory()->equipment()->create(['eqm_prc_code' => 'TH30']);
        $this->makeTag(Equipment::query()->where('eqm_prc_code', 'TH30')->first(), 'TAG-2');

        file_put_contents($this->file, "Unit,Out,In\nTH30,2026-11-10 0:30:00,-\n");

        $this->artisan('rfid:import-sheet-logs', [
            '--file' => $this->file,
            '--aliases' => 'missing.csv',
            '--yard' => 'valenzuela yard',
            '--name-column' => 'Unit',
            '--out-column' => 'Out',
            '--in-column' => 'In',
            '--execute' => true,
        ])->assertSuccessful();

        $this->assertSame(1, RfidRawLog::query()->where('yard_id', $this->yard->id)->count());
    }

    public function test_old_reads_are_accepted_only_with_a_longer_retention_for_the_run(): void
    {
        $asset = Equipment::factory()->equipment()->create(['eqm_prc_code' => 'XBT16#20']);
        $this->makeTag($asset, 'TAG-9');
        $line = 'XBT16 #20,2026-10-30 8:41:00,-';
        config(['rfid.raw_log_retention_days' => 7]);

        $this->run_([$line]);
        $this->assertSame(0, RfidRawLog::query()->count());

        $this->run_([$line], options: ['--retention-days' => 14]);

        $this->assertSame(1, RfidRawLog::query()->where('outcome', RfidRawLog::PROCESSED)->count());
        $this->assertSame(1, AssetMovement::query()->where('asset_id', $asset->eqm_id)->count());
    }

    public function test_rows_are_not_added_while_another_processing_run_holds_the_lock(): void
    {
        $lock = Cache::lock('rfid-raw-log-processing', 600);
        $this->assertTrue($lock->get());
        $added = false;

        try {
            app(RawLogProcessor::class)->addAndRun(function () use (&$added): void {
                $added = true;
            }, waitSeconds: 0);

            $this->fail('Expected the lock to time out.');
        } catch (LockTimeoutException) {
            $this->assertFalse($added);
        } finally {
            $lock->release();
        }
    }

    public function test_rows_added_under_the_lock_are_processed_in_the_same_run(): void
    {
        $asset = Equipment::factory()->equipment()->create(['eqm_prc_code' => 'TH30']);
        $this->makeTag($asset, 'TAG-2');

        $totals = app(RawLogProcessor::class)->addAndRun(fn () => RfidRawLog::query()->insert([
            'batch_uuid' => 'b0000000-0000-0000-0000-000000000000',
            'yard_id' => $this->yard->id,
            'tag_id' => 'TAG-2',
            'status' => 'OUT',
            'detected_at' => '2026-11-10 00:30:00',
        ]));

        $this->assertSame(1, $totals[RfidRawLog::PROCESSED]);
        $this->assertSame(1, AssetMovement::query()->where('asset_id', $asset->eqm_id)->count());
        $this->assertTrue(Cache::lock('rfid-raw-log-processing', 5)->get(), 'the lock is released afterwards');
    }
}

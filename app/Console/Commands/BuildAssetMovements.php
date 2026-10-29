<?php

namespace App\Console\Commands;

use App\Models\AssetMovement;
use App\Models\AssetTagLog;
use App\Services\Rfid\MovementRecorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BuildAssetMovements extends Command
{
    protected $signature = 'rfid:build-movements
        {--dry-run : Show what would be created without writing anything}';

    protected $description = 'Pair the stored tag scans into equipment trips (time out / time in) for the verification screen';

    public function handle(MovementRecorder $recorder): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $totals = ['opened' => 0, 'closed' => 0, 'in_only' => 0, 'ignored' => 0, 'skipped' => 0];

        DB::beginTransaction();

        try {
            // In time order, so an OUT is always paired before the IN that follows it.
            AssetTagLog::query()
                ->orderBy('detected_at')
                ->orderBy('id')
                ->chunk(1000, function ($logs) use ($recorder, &$totals): void {
                    foreach ($recorder->record($logs) as $outcome => $count) {
                        $totals[$outcome] += $count;
                    }
                });
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        $dryRun ? DB::rollBack() : DB::commit();

        $this->table(['Result', 'Count'], [
            ['Scans read', AssetTagLog::query()->count()],
            ['Trips opened by an OUT scan', $totals['opened']],
            ['Trips closed by an IN scan', $totals['closed']],
            ['Trips with an IN scan only (missing time out)', $totals['in_only']],
            ['Scans of the same equipment inside one trip (merged)', $totals['ignored']],
            ['Skipped (not equipment, or already in a trip)', $totals['skipped']],
        ]);

        if ($dryRun) {
            $this->info('Dry run - nothing was written.');

            return self::SUCCESS;
        }

        activity()
            ->event('created')
            ->withProperties($totals)
            ->log('Built '.($totals['opened'] + $totals['in_only']).' equipment trips from tag scans');

        $this->info('Trips in the table now: '.AssetMovement::query()->count());

        return self::SUCCESS;
    }
}

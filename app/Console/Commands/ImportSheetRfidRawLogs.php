<?php

namespace App\Console\Commands;

use App\Jobs\ProcessRfidRawLogs;
use App\Models\Location;
use App\Models\RfidRawLog;
use App\Services\Rfid\RawLogProcessor;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Replays a sheet of missed Time In / Out reads (an item name, a time out and a time in) through the normal RFID
 * process: each time becomes a row in rfid_raw_logs for one of the equipment's tags, exactly as the reader server
 * would have sent it, and RawLogProcessor / MovementRecorder then build the scans and trips.
 *
 * Safe on a database that already has some of the data: a read is left out when the equipment already has a scan
 * or a trip at that time (within the trip window), and the unique key stops the same raw read being stored twice.
 */
class ImportSheetRfidRawLogs extends Command
{
    protected $signature = 'rfid:import-sheet-logs
        {--file=database/data/rfid_sheet_logs.csv : CSV file with a header row}
        {--yard= : The yard (location) the reader is in: its id or its exact name}
        {--name-column=ITEM_NAME : Column with the equipment code or name}
        {--out-column=TIME_OUT : Column with the time out ("-" or empty when there is none)}
        {--in-column=TIME_IN : Column with the time in ("-" or empty when there is none)}
        {--aliases=database/data/aims_code_aliases.csv : CSV of names that have a different code here (AIMS Code, EMMS Code); ignored when missing}
        {--retention-days= : Accept reads this many days old for this run only (default: the RFID_RAW_LOG_RETENTION_DAYS setting); the reads are then processed straight away}
        {--process : Process the reads now instead of leaving them to the queue}
        {--execute : Really add the reads; without it the command only shows what would happen}';

    protected $description = 'Add reads from a Time In / Out sheet to the RFID raw logs so the normal process builds the trips';

    public function handle(RawLogProcessor $processor): int
    {
        $file = $this->path((string) $this->option('file'));

        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $yardId = $this->yardId();

        if ($yardId === null) {
            return self::FAILURE;
        }

        $retentionDays = $this->option('retention-days') !== null ? max(1, (int) $this->option('retention-days')) : RfidRawLog::retentionDays();
        $columns = [
            'name' => (string) $this->option('name-column'),
            'OUT' => (string) $this->option('out-column'),
            'IN' => (string) $this->option('in-column'),
        ];
        $lines = $this->read($file);

        foreach ($columns as $column) {
            if ($lines !== [] && ! array_key_exists($column, $lines[0])) {
                $this->error("The file has no column named {$column}. Columns: ".implode(', ', array_keys($lines[0])));

                return self::FAILURE;
            }
        }

        $plan = $this->plan($lines, $columns, $yardId, $retentionDays);

        $this->report($plan, $retentionDays);

        if (! $this->option('execute')) {
            $this->info('Preview only - nothing was written. Run again with --execute to add the reads.');

            return self::SUCCESS;
        }

        // The processor reads the retention setting, so a longer window for this run has to be set before it runs.
        $overridden = $this->option('retention-days') !== null;
        $processNow = $overridden || $this->option('process');

        if ($overridden) {
            config(['rfid.raw_log_retention_days' => $retentionDays]);
        }

        $inserted = 0;

        foreach (array_chunk(array_values($plan['reads']), 500) as $chunk) {
            $inserted += RfidRawLog::query()->insertOrIgnore($chunk);
        }

        activity()
            ->event('created')
            ->withProperties(['reads_inserted' => $inserted, 'yard_id' => $yardId])
            ->log("Added {$inserted} RFID reads from a Time In / Out sheet");

        $this->info("Added {$inserted} read(s) to the RFID raw logs.");

        if ($processNow) {
            $this->table(['Result', 'Count'], collect($processor->run())->map(fn (int $count, string $outcome): array => [$outcome, $count])->values()->all());
        } elseif ($inserted > 0) {
            ProcessRfidRawLogs::dispatch();
            $this->line('They are processed by the queue, or now with: php artisan rfid:process-raw-logs');
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<array<string, string>>  $lines
     * @param  array{name: string, OUT: string, IN: string}  $columns
     * @return array{reads: array<string, array<string, mixed>>, counts: array<string, int>, missing: array<string, true>, noTag: array<string, true>}
     */
    private function plan(array $lines, array $columns, int $yardId, int $retentionDays): array
    {
        $aliases = $this->aliases();
        $assets = $this->assets();
        $oldest = now()->subDays($retentionDays)->format('Y-m-d H:i:s');
        $latest = now()->format('Y-m-d H:i:s');
        $batch = (string) Str::uuid();

        $counts = ['too old' => 0, 'future' => 0, 'bad time' => 0, 'already recorded' => 0];
        $missing = [];
        $noTag = [];
        $candidates = [];

        foreach ($lines as $line) {
            $name = $line[$columns['name']];
            $code = $this->normalize($name);
            $asset = $assets[$aliases[$code] ?? $code] ?? null;

            if ($asset === null) {
                $missing[$name] = true;

                continue;
            }

            if ($asset['tag'] === null) {
                $noTag[$name] = true;

                continue;
            }

            foreach (['OUT', 'IN'] as $status) {
                $time = $this->time($line[$columns[$status]]);

                match (true) {
                    $time === null => null,
                    $time === false => $counts['bad time']++,
                    $time < $oldest => $counts['too old']++,
                    $time > $latest => $counts['future']++,
                    default => $candidates[] = ['asset' => $asset, 'status' => $status, 'time' => $time],
                };
            }
        }

        $recorded = $this->alreadyRecorded(array_values(array_unique(array_column(array_column($candidates, 'asset'), 'id'))));
        $window = (int) config('rfid.movement_window_seconds');
        $reads = [];

        foreach ($candidates as ['asset' => $asset, 'status' => $status, 'time' => $time]) {
            $at = strtotime($time);

            if (collect($recorded[$asset['id']][$status] ?? [])->contains(fn (int $other): bool => abs($other - $at) <= $window)) {
                $counts['already recorded']++;

                continue;
            }

            $reads[$yardId.'|'.$asset['tag'].'|'.$time.'|'.$status] = [
                'batch_uuid' => $batch,
                'yard_id' => $yardId,
                'tag_id' => $asset['tag'],
                'status' => $status,
                'detected_at' => $time,
            ];
        }

        return ['reads' => $reads, 'counts' => $counts, 'missing' => $missing, 'noTag' => $noTag];
    }

    /**
     * @param  array{reads: array<string, mixed>, counts: array<string, int>, missing: array<string, true>, noTag: array<string, true>}  $plan
     */
    private function report(array $plan, int $retentionDays): void
    {
        $this->table(['Result', 'Count'], [
            ['Reads to add (OUT + IN)', count($plan['reads'])],
            ['Already recorded here (scan or trip at that time, skipped)', $plan['counts']['already recorded']],
            ["Older than {$retentionDays} days (skipped)", $plan['counts']['too old']],
            ['Times in the future (skipped)', $plan['counts']['future']],
            ['Times that could not be read (skipped)', $plan['counts']['bad time']],
            ['Names with no equipment here (skipped)', count($plan['missing'])],
            ['Equipment with no active tag (skipped)', count($plan['noTag'])],
        ]);

        foreach (['missing' => 'Names with no equipment', 'noTag' => 'Equipment with no active tag'] as $key => $label) {
            if ($plan[$key] !== []) {
                $this->warn($label.': '.implode(', ', array_keys($plan[$key])));
            }
        }

        if ($plan['counts']['too old'] > 0) {
            $this->warn('Some reads are older than the retention period. Add --retention-days=<days> to accept them for this run.');
        }
    }

    private function yardId(): ?int
    {
        $yard = trim((string) $this->option('yard'));
        $matches = match (true) {
            $yard === '' => collect(),
            ctype_digit($yard) => Location::query()->whereKey((int) $yard)->get(),
            default => Location::query()->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($yard)])->get(),
        };

        if ($matches->count() === 1) {
            return (int) $matches->first()->getKey();
        }

        $this->error($matches->isEmpty()
            ? 'Pass --yard=<location id or exact name>. Yards: '.Location::query()->orderBy('id')->limit(15)->get(['id', 'name'])->map(fn (Location $location): string => "{$location->id} = ".trim($location->name))->implode(', ')
            : "More than one yard is named \"{$yard}\"; use its id.");

        return null;
    }

    /**
     * Normalized equipment code (or, when no code matches, name) => the unit and one active tag of it. A code or name
     * shared by two units is left out rather than guessed.
     *
     * @return array<string, array{id: int, tag: ?string}>
     */
    private function assets(): array
    {
        $tags = DB::table('asset_tags')->where('is_deleted', false)->orderBy('tag_id')->pluck('tag_id', 'asset_parent_id');
        $byCode = [];
        $byName = [];

        foreach (DB::table('equipment_units')->get(['eqm_id', 'eqm_prc_code', 'eqm_name']) as $unit) {
            $entry = ['id' => (int) $unit->eqm_id, 'tag' => $tags[$unit->eqm_id] ?? null];

            if (filled($unit->eqm_prc_code)) {
                $key = $this->normalize($unit->eqm_prc_code);
                $byCode[$key] = isset($byCode[$key]) ? false : $entry;
            }

            if (filled($unit->eqm_name)) {
                $key = $this->normalize($unit->eqm_name);
                $byName[$key] = isset($byName[$key]) ? false : $entry;
            }
        }

        return array_filter($byCode + $byName);
    }

    /**
     * Times the given equipment already has, per status: its scans (any tag) and its trips.
     *
     * @param  list<int>  $assetIds
     * @return array<int, array{OUT?: list<int>, IN?: list<int>}>
     */
    private function alreadyRecorded(array $assetIds): array
    {
        $recorded = [];

        foreach (array_chunk($assetIds, 500) as $chunk) {
            $tagOwner = DB::table('asset_tags')->whereIn('asset_parent_id', $chunk)->pluck('asset_parent_id', 'tag_id');

            foreach (array_chunk($tagOwner->keys()->all(), 500) as $tagChunk) {
                foreach (DB::table('asset_tag_logs')->whereIn('tag_id', $tagChunk)->get(['tag_id', 'status', 'detected_at']) as $scan) {
                    $recorded[$tagOwner[$scan->tag_id]][strtoupper($scan->status)][] = strtotime($scan->detected_at);
                }
            }

            foreach (DB::table('asset_movements')->whereIn('asset_id', $chunk)->whereNull('disregarded_at')->get(['asset_id', 'time_out', 'time_in']) as $trip) {
                foreach (['OUT' => $trip->time_out, 'IN' => $trip->time_in] as $status => $time) {
                    if ($time !== null) {
                        $recorded[$trip->asset_id][$status][] = strtotime($time);
                    }
                }
            }
        }

        return $recorded;
    }

    /**
     * @return array<string, string> normalized name => normalized code here
     */
    private function aliases(): array
    {
        $file = $this->path((string) $this->option('aliases'));

        if (! is_file($file)) {
            return [];
        }

        $aliases = [];

        foreach ($this->read($file) as $line) {
            $aliases[$this->normalize($line['AIMS Code'] ?? '')] = $this->normalize($line['EMMS Code'] ?? '');
        }

        return $aliases;
    }

    /**
     * @return list<array<string, string>>
     */
    private function read(string $file): array
    {
        $handle = fopen($file, 'r');
        $header = array_map(fn (?string $name): string => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $name)), (array) fgetcsv($handle));
        $lines = [];

        while (($values = fgetcsv($handle)) !== false) {
            if ($values === [null]) {
                continue;
            }

            $lines[] = array_combine($header, array_map('strval', array_pad(array_slice($values, 0, count($header)), count($header), '')));
        }

        fclose($handle);

        return $lines;
    }

    /**
     * @return string|false|null a time, null when there is none ("-" or empty), false when it cannot be read
     */
    private function time(string $value): string|false|null
    {
        $value = trim($value);

        if ($value === '' || $value === '-') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return false;
        }
    }

    private function normalize(string $code): string
    {
        return mb_strtolower((string) preg_replace('/\s+/', '', $code));
    }

    private function path(string $path): string
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) ? $path : base_path($path);
    }
}

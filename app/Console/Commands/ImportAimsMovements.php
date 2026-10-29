<?php

namespace App\Console\Commands;

use App\Services\Aims\AimsMovementImporter;
use App\Services\Aims\AimsUserMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportAimsMovements extends Command
{
    protected $signature = 'movements:import-aims
        {--since= : Only trips from this date on, by their time out (or time in); default: two years ago}
        {--execute : Really write the trips; without it the command only shows what would happen}
        {--rollback : Remove every trip that was imported from AIMS (use with --execute)}
        {--aliases=database/data/aims_code_aliases.csv : CSV of AIMS codes that have a different code in EMMS (AIMS Code, EMMS Code)}
        {--report=aims-movements-skipped.csv : CSV (inside storage/app) listing every AIMS log that was not imported}';

    protected $description = 'Copy equipment trips (time out / time in) from the AIMS LOGS table into asset_movements';

    public function handle(AimsMovementImporter $importer, AimsUserMapper $userMapper): int
    {
        if ($this->option('rollback')) {
            return $this->rollback($importer);
        }

        $since = $this->option('since') ? now()->parse((string) $this->option('since'))->startOfDay() : now()->subYears(2)->startOfDay();
        $aims = DB::connection(env('DB_AIMS_CONNECTION', 'sqlsrv_aims'));

        $logs = $aims->table('LOGS')
            ->whereRaw('COALESCE(TIME_OUT, TIME_IN) >= ?', [$since->format('Y-m-d H:i:s')])
            ->orderBy('ID')
            ->get();
        $parents = $aims->table('PARENT')->get(['ID', 'NAME']);
        $accessories = $aims->table('ACCESSORIES')->get(['ID']);

        $unitsByCode = [];
        $types = DB::table('asset_types')->pluck('code', 'id');

        foreach (DB::table('equipment_units')->whereNotNull('eqm_prc_code')->get(['eqm_id', 'eqm_prc_code', 'asset_type_id']) as $unit) {
            $unitsByCode[$this->normalize($unit->eqm_prc_code)][] = ['id' => (int) $unit->eqm_id, 'type' => $types[$unit->asset_type_id] ?? null];
        }

        $aliases = collect($this->readCsv((string) $this->option('aliases')))
            ->filter(fn (array $row): bool => trim($row['AIMS Code'] ?? '') !== '' && trim($row['EMMS Code'] ?? '') !== '')
            ->mapWithKeys(fn (array $row): array => [$row['AIMS Code'] => $row['EMMS Code']])
            ->all();

        $locationMap = $this->locationMap($aims);
        $alreadyImported = DB::table('asset_movements')->whereNotNull('aims_log_id')->pluck('aims_log_id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();

        // Who did the manual entries and acknowledgements in AIMS, as EMMS users.
        $referenced = $logs->flatMap(fn (object $log): array => [$log->MANUAL_IN_BY ?? null, $log->MANUAL_OUT_BY ?? null, $log->ACKNOWLEDGED_BY ?? null])->filter()->unique()->values()->all();
        $aimsUsers = $aims->table('USERS')->get(['ID', 'FULLNAME', 'EMAIL_ADDRESS']);
        $users = $userMapper->plan($aimsUsers, DB::table('app_users')->get(['user_id', 'user_fname', 'user_lname', 'user_email']), $referenced);

        $plan = $importer->plan($logs, $parents, $accessories, $unitsByCode, $aliases, $users['map'], $locationMap, $alreadyImported);

        $this->report($since, $logs->count(), $plan, $users, $locationMap, $aimsUsers->pluck('FULLNAME', 'ID'));

        $reportPath = storage_path('app/'.ltrim((string) $this->option('report'), '/\\'));
        $this->writeReport($reportPath, $plan['skipped']);
        $this->line("Skipped report: {$reportPath}");

        if (! $this->option('execute')) {
            $this->info('Preview only - nothing was written. Run again with --execute to import.');

            return self::SUCCESS;
        }

        $departmentId = DB::table('departments')->where('dep_code', 'AIMS')->value('dep_id');

        if ($users['create'] !== [] && $departmentId === null) {
            $this->error('There is no AIMS department (dep_code AIMS) to put the new users in.');

            return self::FAILURE;
        }

        [$created, $inserted] = DB::transaction(function () use ($importer, $userMapper, $users, $departmentId, $logs, $parents, $accessories, $unitsByCode, $aliases, $locationMap, $alreadyImported): array {
            $created = $users['create'] === [] ? [] : $userMapper->create($users['create'], (int) $departmentId);
            $plan = $importer->plan($logs, $parents, $accessories, $unitsByCode, $aliases, $users['map'] + $created, $locationMap, $alreadyImported);

            return [$created, $importer->persist($plan['rows'])];
        });

        activity()
            ->event('created')
            ->withProperties(['trips_inserted' => $inserted, 'since' => $since->toDateString(), 'users_created' => array_values($created)])
            ->log("Imported {$inserted} equipment trips from AIMS (since {$since->toDateString()})");

        $this->info("Imported {$inserted} trip(s) and created ".count($created).' AIMS user(s). Undo with: php artisan movements:import-aims --rollback --execute');

        return self::SUCCESS;
    }

    private function rollback(AimsMovementImporter $importer): int
    {
        $count = DB::table('asset_movements')->whereNotNull('aims_log_id')->count();
        $this->line("Trips imported from AIMS: {$count}");

        if (! $this->option('execute')) {
            $this->info('Preview only - nothing was removed. Run again with --rollback --execute to remove them.');

            return self::SUCCESS;
        }

        $this->info('Removed '.$importer->rollback().' trip(s) that came from AIMS.');

        return self::SUCCESS;
    }

    /**
     * @param  array{rows: list<array<string, mixed>>, skipped: list<array<string, mixed>>, unmappedUsers: list<int>}  $plan
     * @param  array{map: array<int, int>, create: array<int, array<string, mixed>>, ambiguous: array<int, mixed>}  $users
     * @param  array<int, int>  $locationMap
     */
    private function report($since, int $logCount, array $plan, array $users, array $locationMap, $aimsNames): void
    {
        $rows = collect($plan['rows']);
        $complete = $rows->filter(fn (array $r): bool => $r['time_out'] !== null && $r['time_in'] !== null);

        $this->table(['Result', 'Count'], [
            ['AIMS logs read (from '.$since->toDateString().')', $logCount],
            ['Trips to import', $rows->count()],
            ['  complete (time out and time in)', $complete->count()],
            ['  time in only (missing time out)', $rows->filter(fn (array $r): bool => $r['time_out'] === null)->count()],
            ['  never closed, latest movement of the equipment', $rows->filter(fn (array $r): bool => $r['time_in'] === null)->count()],
            ['  acknowledged in AIMS (kept)', $rows->filter(fn (array $r): bool => $r['acknowledged_at'] !== null)->count()],
            ['  entered manually in AIMS (kept)', $rows->filter(fn (array $r): bool => $r['manual_out_by'] !== null || $r['manual_in_by'] !== null)->count()],
            ['Not imported', count($plan['skipped'])],
        ]);

        collect($plan['skipped'])->countBy('reason')->sortDesc()->each(
            fn (int $count, string $reason) => $this->line("  not imported {$count}: {$reason}")
        );

        $this->line('AIMS locations mapped to EMMS locations: '.json_encode($locationMap));

        if ($users['create'] !== [] || $users['ambiguous'] !== [] || $plan['unmappedUsers'] !== []) {
            $this->warn('Users: '.count($users['create']).' AIMS user(s) will be created (inactive, AIMS department), '.count($users['ambiguous']).' are ambiguous and stay empty.');
        } else {
            $this->line('All AIMS users on these trips are already EMMS users.');
        }
    }

    private function normalize(string $code): string
    {
        return mb_strtolower((string) preg_replace('/\s+/', '', $code));
    }

    /**
     * AIMS yard id => EMMS location id, matched by name ("VALENZUELA YARD" => "VALENZUELA"); roots win over children.
     *
     * @return array<int, int>
     */
    private function locationMap($aims): array
    {
        $emms = DB::table('locations')->orderByRaw('parent_id IS NULL DESC')->orderBy('id')->get(['id', 'name'])
            ->reverse()
            ->mapWithKeys(fn (object $location): array => [mb_strtoupper(trim($location->name)) => (int) $location->id]);

        $map = [];

        foreach ($aims->table('LOCATION')->get(['ID', 'LOCATION_NAME']) as $location) {
            $name = mb_strtoupper(trim((string) preg_replace('/\s*YARD\s*$/i', '', (string) $location->LOCATION_NAME)));

            if (isset($emms[$name])) {
                $map[(int) $location->ID] = $emms[$name];
            }
        }

        return $map;
    }

    /**
     * @return list<array<string, string>>
     */
    private function readCsv(string $path): array
    {
        $path = File::exists($path) ? $path : base_path($path);

        if (! File::exists($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        $header = array_map(fn ($h) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), fgetcsv($handle, 0, ',', '"', '') ?: []);
        $rows = [];

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $rows[] = array_combine($header, array_pad($row, count($header), ''));
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $skipped
     */
    private function writeReport(string $path, array $skipped): void
    {
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'w');
        fputcsv($handle, ['aims_log_id', 'reason', 'aims_asset', 'time_out', 'time_in'], escape: '');

        foreach ($skipped as $row) {
            fputcsv($handle, [$row['aims_id'], $row['reason'], $row['asset'], $row['time_out'], $row['time_in']], escape: '');
        }

        fclose($handle);
    }
}

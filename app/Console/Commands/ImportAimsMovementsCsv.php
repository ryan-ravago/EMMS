<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use App\Models\Location;
use App\Services\Aims\AimsMovementCsv;
use App\Services\Aims\AimsMovementImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportAimsMovementsCsv extends Command
{
    protected $signature = 'movements:import-aims-csv
        {--file=database/data/aims_movements.csv : The trips file written by movements:export-aims-csv}
        {--users-file=database/data/aims_movement_users.csv : The people named on the trips}
        {--create-users : Create the people that do not exist here yet (inactive, in the AIMS department) so their acknowledgements and manual entries are kept}
        {--execute : Really write the trips; without it the command only shows what would happen}
        {--rollback : Remove every trip that was imported from AIMS (use with --execute)}';

    protected $description = 'Add the equipment trips copied from AIMS (from a CSV file) to this database; needs no AIMS connection';

    public function handle(AimsMovementCsv $csv, AimsMovementImporter $importer): int
    {
        if (! Schema::hasColumn('asset_movements', 'aims_log_id')) {
            $this->error('This database is not up to date. Run: php artisan migrate');

            return self::FAILURE;
        }

        if ($this->option('rollback')) {
            return $this->rollback($importer);
        }

        $file = $this->path((string) $this->option('file'));
        $usersFile = $this->path((string) $this->option('users-file'));

        if (! is_file($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $assetIds = DB::table('equipment_units')->whereNotNull('eqm_prc_code')->pluck('eqm_id', 'eqm_prc_code')
            ->mapWithKeys(fn ($id, $code) => [mb_strtolower(trim($code)) => (int) $id])->all();
        $userIds = DB::table('app_users')->whereNotNull('user_email')->pluck('user_id', 'user_email')
            ->mapWithKeys(fn ($id, $email) => [mb_strtolower(trim($email)) => (int) $id])->all();
        $yardIds = collect(Location::pathMap())
            ->mapWithKeys(fn (string $path, int $id) => [mb_strtoupper(trim($path)) => $id])->all();
        $imported = DB::table('asset_movements')->whereNotNull('aims_log_id')->pluck('aims_log_id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])->all();

        // People the trips name that this database does not have.
        $toCreate = [];

        if ($this->option('create-users') && is_file($usersFile)) {
            foreach ($csv->read($usersFile, AimsMovementCsv::USER_COLUMNS) as $user) {
                $email = mb_strtolower(trim($user['email']));

                if ($email !== '' && ! isset($userIds[$email])) {
                    $toCreate[$email] = $user;
                }
            }
        }

        $departmentId = DB::table('departments')->where('dep_code', 'AIMS')->value('dep_id');

        if ($toCreate !== [] && $departmentId === null) {
            $this->error('There is no AIMS department (dep_code AIMS) to put the new people in.');

            return self::FAILURE;
        }

        // For the preview the people to be created count as present; they get real ids when written.
        $plan = $csv->plan($csv->read($file), $assetIds, $userIds + array_fill_keys(array_keys($toCreate), 0), $yardIds, $imported);

        $this->report($plan, $toCreate);

        if (! $this->option('execute')) {
            $this->info('Preview only - nothing was written. Run again with --execute to add the trips.');

            return self::SUCCESS;
        }

        $inserted = DB::transaction(function () use ($csv, $importer, $toCreate, $departmentId, $file, $assetIds, $userIds, $yardIds, $imported): int {
            foreach ($toCreate as $email => $user) {
                $userIds[$email] = (int) AppUser::query()->create([
                    'user_fname' => $user['first_name'],
                    'user_mname' => $user['middle_name'] !== '' ? $user['middle_name'] : null,
                    'user_lname' => $user['last_name'],
                    'user_email' => $email,
                    'user_dep_id' => $departmentId,
                    'is_active' => false,
                ])->getKey();
            }

            return $importer->persist($csv->plan($csv->read($file), $assetIds, $userIds, $yardIds, $imported)['rows']);
        });

        activity()
            ->event('created')
            ->withProperties(['trips_inserted' => $inserted, 'users_created' => count($toCreate)])
            ->log("Imported {$inserted} equipment trips copied from AIMS");

        $this->info("Added {$inserted} trip(s) and created ".count($toCreate).' person(s). Undo with: php artisan movements:import-aims-csv --rollback --execute');

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
     * @param  array{rows: list<array<string, mixed>>, skipped: array<string, int>, missingEquipment: list<string>, missingUsers: list<string>, missingYards: list<string>}  $plan
     * @param  array<string, array<string, string>>  $toCreate
     */
    private function report(array $plan, array $toCreate): void
    {
        $this->table(['Result', 'Count'], [
            ['Trips to add', count($plan['rows'])],
            ['Already here (skipped)', $plan['skipped']['already imported']],
            ['Equipment not found here (skipped)', $plan['skipped']['equipment not found']],
            ['People to create', count($toCreate)],
        ]);

        foreach ([
            'Equipment codes not found' => $plan['missingEquipment'],
            'People not found (their acknowledgements / manual entries are left out; use --create-users)' => $plan['missingUsers'],
            'Yards not found (the trip is added without a yard)' => $plan['missingYards'],
        ] as $label => $items) {
            if ($items !== []) {
                $this->warn($label.' ('.count($items).'): '.implode(', ', array_slice($items, 0, 8)).(count($items) > 8 ? ', ...' : ''));
            }
        }
    }

    private function path(string $path): string
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) ? $path : base_path($path);
    }
}

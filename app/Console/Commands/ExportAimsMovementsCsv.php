<?php

namespace App\Console\Commands;

use App\Services\Aims\AimsMovementCsv;
use Illuminate\Console\Command;

class ExportAimsMovementsCsv extends Command
{
    protected $signature = 'movements:export-aims-csv
        {--file=database/data/aims_movements.csv : Where to write the trips}
        {--users-file=database/data/aims_movement_users.csv : Where to write the people named on them}';

    protected $description = 'Write the trips imported from AIMS to a CSV file, so another EMMS database (production) can load them without AIMS';

    public function handle(AimsMovementCsv $csv): int
    {
        $file = $this->path((string) $this->option('file'));
        $usersFile = $this->path((string) $this->option('users-file'));

        $result = $csv->export($file, $usersFile);

        $this->table(['Written', 'Count'], [
            ['Trips', $result['trips']],
            ['People named on them', $result['users']],
            ['Verifications/manual entries without an email (cannot travel)', $result['noEmail']],
        ]);
        $this->line("Trips:  {$file}");
        $this->line("People: {$usersFile}");
        $this->info('Commit both files, push, and on production run: php artisan movements:import-aims-csv --create-users --execute');

        return self::SUCCESS;
    }

    private function path(string $path): string
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) ? $path : base_path($path);
    }
}

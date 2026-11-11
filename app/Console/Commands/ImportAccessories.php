<?php

namespace App\Console\Commands;

use App\Models\AssetType;
use App\Models\Equipment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportAccessories extends Command
{
    protected $signature = 'assets:import-accessories
        {source=database/data/accessories_to_add.csv : CSV with an "ACCESSORY NAME" column (other columns are ignored)}
        {--execute : Really add the accessories; without it the command only shows what would be added}
        {--force : Skip the confirmation question (with --execute)}';

    protected $description = 'Add accessories listed by name (sheet "ACCESSORIES TO BE ADDED TO EMS") to equipment_units, with no parent';

    private const NAME_COLUMN = 'ACCESSORY NAME';

    private const MAX_CODE_LENGTH = 30;

    public function handle(): int
    {
        $source = (string) $this->argument('source');
        $path = File::exists($source) ? $source : base_path($source);

        if (! File::exists($path)) {
            $this->error("File not found: {$source}");

            return self::FAILURE;
        }

        $names = $this->readNames($path);

        if ($names === null) {
            $this->error('The file has no "'.self::NAME_COLUMN.'" column.');

            return self::FAILURE;
        }

        // The accessory name is also its asset code (eqm_prc_code), so "already in EMMS" is judged on the code,
        // ignoring case and spaces, the same way the AIMS imports match codes.
        $existing = DB::table('equipment_units')->pluck('eqm_prc_code')
            ->mapWithKeys(fn ($code) => [$this->normalize((string) $code) => (string) $code]);

        $toAdd = [];
        $skipped = [];
        $seen = [];

        foreach ($names as $line => $name) {
            $key = $this->normalize($name);

            $reason = match (true) {
                mb_strlen($name) > self::MAX_CODE_LENGTH => 'name is longer than '.self::MAX_CODE_LENGTH.' characters (the asset code limit)',
                $existing->has($key) => 'already in EMMS',
                isset($seen[$key]) => 'listed twice in the file',
                default => null,
            };

            if ($reason !== null) {
                $skipped[] = [
                    'line' => $line,
                    'name' => $name,
                    'reason' => $reason,
                    'existing' => $reason === 'already in EMMS' ? $existing[$key] : '',
                ];

                continue;
            }

            $seen[$key] = true;
            $toAdd[] = $name;
        }

        $this->table(['Result', 'Count'], [
            ['Names in '.$source, count($toAdd) + count($skipped)],
            ['To add', count($toAdd)],
            ['Skipped', count($skipped)],
        ]);

        foreach (collect($skipped)->groupBy('reason') as $reason => $rows) {
            $this->line("  skipped {$rows->count()}: {$reason}");

            foreach ($rows->take(5) as $row) {
                $this->line("      line {$row['line']}: {$row['name']}".($row['existing'] !== '' ? " (as {$row['existing']})" : ''));
            }
        }

        if ($skipped !== []) {
            $this->line('  Every skipped name: '.$this->writeSkippedReport($skipped));
        }

        if ($toAdd === []) {
            $this->info('Nothing to add.');

            return self::SUCCESS;
        }

        if (! $this->option('execute')) {
            $this->info('Preview only. Run again with --execute to add them.');

            return self::SUCCESS;
        }

        $accessoryTypeId = AssetType::accessoryId();

        if ($accessoryTypeId === null) {
            $this->error('The accessory asset type (code '.AssetType::ACCESSORY.') is missing in asset_types.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Add '.count($toAdd).' accessories to EMMS?', false)) {
            $this->warn('Cancelled. Nothing was added.');

            return self::FAILURE;
        }

        $created = DB::transaction(fn (): array => collect($toAdd)->map(fn (string $name): array => [
            'id' => Equipment::query()->create([
                'eqm_prc_code' => $name,
                'eqm_name' => $name,
                'asset_type_id' => $accessoryTypeId,
                'eqm_is_active' => true,
            ])->getKey(),
            'name' => $name,
        ])->all());

        $report = $this->writeReport($created);

        activity()
            ->event('created')
            ->withProperties(['accessories' => count($created), 'report' => $report])
            ->log('Added '.count($created).' accessories from the accessories list');

        $this->info('Added '.count($created).' accessories. Report: '.$report);

        return self::SUCCESS;
    }

    /**
     * The values of the "ACCESSORY NAME" column, by line number, with spaces tidied. Null when the column is missing.
     *
     * @return array<int, string>|null
     */
    private function readNames(string $path): ?array
    {
        $handle = fopen($path, 'r');
        $header = array_map(
            fn ($h) => mb_strtoupper(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))),
            fgetcsv($handle, 0, ',', '"', '') ?: []
        );
        $column = array_search(self::NAME_COLUMN, $header, true);

        if ($column === false) {
            fclose($handle);

            return null;
        }

        $names = [];
        $line = 1;

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $line++;
            $name = trim((string) preg_replace('/\s+/u', ' ', (string) ($row[$column] ?? '')));

            // Rows with no name (the sheet runs to row 1000, with numbers filled down) are empty, not mistakes.
            if ($name === '') {
                continue;
            }

            $names[$line] = $name;
        }

        fclose($handle);

        return $names;
    }

    private function normalize(string $code): string
    {
        return mb_strtolower((string) preg_replace('/\s+/', '', $code));
    }

    /**
     * @param  list<array{line: int, name: string, reason: string, existing: string}>  $skipped
     */
    private function writeSkippedReport(array $skipped): string
    {
        $directory = storage_path('app/asset-imports');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.now()->format('Ymd_His').'_skipped_accessories.csv';

        $handle = fopen($path, 'w');
        fputcsv($handle, ['line', 'accessory_name', 'reason', 'existing_code'], escape: '');

        foreach ($skipped as $row) {
            fputcsv($handle, [$row['line'], $row['name'], $row['reason'], $row['existing']], escape: '');
        }

        fclose($handle);

        return $path;
    }

    /**
     * @param  list<array{id: int|string, name: string}>  $created
     */
    private function writeReport(array $created): string
    {
        $directory = storage_path('app/asset-imports');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.now()->format('Ymd_His').'_added_accessories.csv';

        $handle = fopen($path, 'w');
        fputcsv($handle, ['eqm_id', 'asset_code', 'asset_name'], escape: '');

        foreach ($created as $accessory) {
            fputcsv($handle, [$accessory['id'], $accessory['name'], $accessory['name']], escape: '');
        }

        fclose($handle);

        return $path;
    }
}

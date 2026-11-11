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
        {source=database/data/accessories_to_add.csv : CSV with an "ACCESSORY NAME" column and, optionally, an "ASSET CODE" column (the name is used as the code when there is no such column)}
        {--execute : Really add the accessories; without it the command only shows what would be added}
        {--force : Skip the confirmation question (with --execute)}';

    protected $description = 'Add accessories (sheet "ACCESSORIES TO BE ADDED TO EMS") to equipment_units, with no parent';

    private const NAME_COLUMN = 'ACCESSORY NAME';

    private const CODE_COLUMN = 'ASSET CODE';

    private const MAX_CODE_LENGTH = 255;

    public function handle(): int
    {
        $source = (string) $this->argument('source');
        $path = File::exists($source) ? $source : base_path($source);

        if (! File::exists($path)) {
            $this->error("File not found: {$source}");

            return self::FAILURE;
        }

        $rows = $this->readRows($path);

        if ($rows === null) {
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

        foreach ($rows as $line => ['name' => $name, 'code' => $code]) {
            $key = $this->normalize($code);

            $reason = match (true) {
                $code === '' => 'no asset code (the ASSET CODE column is empty for this row)',
                mb_strlen($code) > self::MAX_CODE_LENGTH => 'asset code is longer than '.self::MAX_CODE_LENGTH.' characters',
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
            $toAdd[] = ['code' => $code, 'name' => $name];
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

        $created = DB::transaction(fn (): array => collect($toAdd)->map(fn (array $accessory): array => [
            'id' => Equipment::query()->create([
                'eqm_prc_code' => $accessory['code'],
                'eqm_name' => mb_substr($accessory['name'], 0, 255),
                'asset_type_id' => $accessoryTypeId,
                'eqm_is_active' => true,
            ])->getKey(),
        ] + $accessory)->all());

        $report = $this->writeReport($created);

        activity()
            ->event('created')
            ->withProperties(['accessories' => count($created), 'report' => $report])
            ->log('Added '.count($created).' accessories from the accessories list');

        $this->info('Added '.count($created).' accessories. Report: '.$report);

        return self::SUCCESS;
    }

    /**
     * The accessory name and asset code of each row, by line number, with spaces tidied. The code comes from the
     * "ASSET CODE" column when the file has one, otherwise it is the name. Null when the name column is missing.
     *
     * @return array<int, array{name: string, code: string}>|null
     */
    private function readRows(string $path): ?array
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

        $codeColumn = array_search(self::CODE_COLUMN, $header, true);
        $rows = [];
        $line = 1;

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $line++;
            $name = $this->tidy((string) ($row[$column] ?? ''));

            // Rows with no name (the sheet runs to row 1000, with numbers filled down) are empty, not mistakes.
            if ($name === '') {
                continue;
            }

            $rows[$line] = [
                'name' => $name,
                'code' => $codeColumn === false ? $name : $this->tidy((string) ($row[$codeColumn] ?? '')),
            ];
        }

        fclose($handle);

        return $rows;
    }

    private function tidy(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
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
     * @param  list<array{id: int|string, code: string, name: string}>  $created
     */
    private function writeReport(array $created): string
    {
        $directory = storage_path('app/asset-imports');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.now()->format('Ymd_His').'_added_accessories.csv';

        $handle = fopen($path, 'w');
        fputcsv($handle, ['eqm_id', 'asset_code', 'asset_name'], escape: '');

        foreach ($created as $accessory) {
            fputcsv($handle, [$accessory['id'], $accessory['code'], $accessory['name']], escape: '');
        }

        fclose($handle);

        return $path;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\Assets\AssetPurger;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PurgeAssets extends Command
{
    protected $signature = 'assets:purge
        {source=database/data/assets_to_delete.csv : CSV file path (default: the list kept in the project), or a Google Sheets link shared as "anyone with the link"}
        {--column=Asset Code : Header of the column that holds the asset codes}
        {--include-typed : Also delete assets that have an asset type (shown on the Equipment, Accessories or Service Vehicles pages)}
        {--execute : Really delete; without it the command only shows what would be deleted}
        {--force : Skip the confirmation question (with --execute)}';

    protected $description = 'Delete the assets listed in a sheet (matched by asset code) and all of their related data';

    public function handle(AssetPurger $purger): int
    {
        try {
            $rows = $this->readRows((string) $this->argument('source'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $codeColumn = (string) $this->option('column');
        $reasonColumn = collect(array_keys($rows->first() ?? []))->first(fn (string $header): bool => str_starts_with(mb_strtolower($header), 'reason'));
        $reasons = $rows->mapWithKeys(fn (array $row): array => [(string) $row[$codeColumn] => $reasonColumn ? (string) $row[$reasonColumn] : ''])->all();
        $codes = array_keys($reasons);

        $units = DB::table('equipment_units')->get(['eqm_id', 'eqm_prc_code', 'eqm_name', 'asset_type_id', 'eqm_is_active'])->keyBy('eqm_id');
        $match = $purger->matchCodes(array_map('strval', $codes), $units);

        $typeNames = DB::table('asset_types')->pluck('name', 'id');
        $targets = collect();

        foreach ($match['exact'] as $code => $id) {
            $targets->push(['code' => (string) $code, 'id' => $id, 'match' => 'exact']);
        }

        foreach ($match['spacing'] as $code => $found) {
            $targets->push(['code' => (string) $code, 'id' => $found['id'], 'match' => "spacing differs (database: \"{$found['code']}\")"]);
        }

        $targets = $targets->map(function (array $target) use ($units, $reasons, $typeNames): array {
            $unit = $units[$target['id']];

            return $target + [
                'name' => (string) $unit->eqm_name,
                'type' => $unit->asset_type_id ? (string) ($typeNames[$unit->asset_type_id] ?? $unit->asset_type_id) : '',
                'reason' => (string) ($reasons[$target['code']] ?? ''),
            ];
        });

        [$typed, $untyped] = $targets->partition(fn (array $target): bool => $target['type'] !== '');
        $toDelete = $this->option('include-typed') ? $targets : $untyped;

        $this->info('Codes in the sheet: '.count($codes));
        $this->table(['Result', 'Count'], [
            ['Matched exactly', count($match['exact'])],
            ['Matched after ignoring extra spaces', count($match['spacing'])],
            ['Not found', count($match['missing'])],
            ['Ambiguous (left alone)', count($match['ambiguous'])],
            ['Matched assets with an asset type', $typed->count()],
            ['Assets to delete', $toDelete->count()],
        ]);

        if ($match['spacing'] !== []) {
            $this->line('Matched after ignoring extra spaces:');
            $this->table(['Sheet', 'Database'], collect($match['spacing'])->map(fn (array $found, string $code): array => [$code, $found['code']])->values()->all());
        }

        if ($match['missing'] !== []) {
            $this->warn('Not found: '.implode(', ', $match['missing']));
        }

        foreach ($match['ambiguous'] as $code => $candidates) {
            $this->warn("Ambiguous, skipped: \"{$code}\" matches ".implode(', ', array_map(fn (string $c): string => "\"{$c}\"", $candidates)));
        }

        if ($typed->isNotEmpty()) {
            $this->line('');
            $this->warn($this->option('include-typed')
                ? 'These assets have an asset type and WILL be deleted (--include-typed):'
                : 'These assets have an asset type and are NOT deleted. Add --include-typed to delete them too:');
            $this->table(['Code', 'Name', 'Type'], $typed->map(fn (array $t): array => [$t['code'], $t['name'], $t['type']])->values()->all());
        }

        $assetIds = $toDelete->pluck('id')->all();
        $workOrders = $assetIds === [] ? collect() : DB::table('work_orders')->whereIn('wo_eqm_id', $assetIds)->get(['wo_id', 'wo_no', 'wo_eqm_id', 'wo_status_id']);

        $this->line('');
        $this->line($this->option('execute') ? 'Rows that will be removed:' : 'Rows that would be removed (preview, nothing is changed):');
        $preview = $purger->preview($assetIds);
        $this->table(['Data', 'Rows'], collect($preview)->filter()->map(fn (int $count, string $label): array => [$label, $count])->values()->all());

        if ($workOrders->isNotEmpty()) {
            $this->warn("{$workOrders->count()} work orders will be deleted with their history: ".$workOrders->pluck('wo_no')->implode(', '));
        }

        if ($assetIds === []) {
            $this->info('Nothing to delete.');

            return self::SUCCESS;
        }

        if (! $this->option('execute')) {
            $this->info('Preview only. Run again with --execute to delete.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Permanently delete {$toDelete->count()} assets and all of their related data? This cannot be undone.", false)) {
            $this->warn('Cancelled. Nothing was deleted.');

            return self::FAILURE;
        }

        $deleted = $purger->purge($assetIds);
        $report = $this->writeReport($toDelete, $workOrders, $units);

        activity()
            ->event('deleted')
            ->withProperties(['assets' => $toDelete->count(), 'rows' => array_filter($deleted), 'report' => $report])
            ->log("Purged {$toDelete->count()} assets and their related data from a delete list");

        $this->table(['Data', 'Rows removed'], collect($deleted)->filter()->map(fn (int $count, string $label): array => [$label, $count])->values()->all());
        $this->info("Done. {$toDelete->count()} assets deleted. Report of what was deleted: {$report}");

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function readRows(string $source): Collection
    {
        if (preg_match('~docs\.google\.com/spreadsheets/d/([\w-]+)~', $source, $sheet)) {
            preg_match('~[#&?]gid=(\d+)~', $source, $gid);
            $source = "https://docs.google.com/spreadsheets/d/{$sheet[1]}/export?format=csv&gid=".($gid[1] ?? '0');
        }

        if (preg_match('~^https?://~i', $source)) {
            $response = Http::timeout(60)->get($source);

            if (! $response->successful() || str_contains((string) $response->header('Content-Type'), 'text/html')) {
                throw new RuntimeException("Could not download the sheet as CSV ({$response->status()}). Share it as \"anyone with the link can view\" or pass a CSV file instead.");
            }

            $csv = $response->body();
        } else {
            $path = File::exists($source) ? $source : base_path($source);

            if (! File::exists($path)) {
                throw new RuntimeException("File not found: {$source}");
            }

            $csv = File::get($path);
        }

        $lines = preg_split('/\r\n|\n|\r/', trim((string) preg_replace('/^\xEF\xBB\xBF/', '', $csv)));
        $header = array_map('trim', str_getcsv((string) array_shift($lines), escape: ''));
        $column = (string) $this->option('column');

        if (! in_array($column, $header, true)) {
            throw new RuntimeException("Column \"{$column}\" not found. Columns in the sheet: ".implode(', ', $header));
        }

        return collect($lines)
            ->filter(fn (string $line): bool => trim($line) !== '')
            ->map(fn (string $line): array => array_combine($header, array_pad(array_map('trim', str_getcsv($line, escape: '')), count($header), '')))
            ->filter(fn (array $row): bool => $row[$column] !== '')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $assets
     * @param  Collection<int, object>  $workOrders
     * @param  Collection<int|string, object>  $units
     */
    private function writeReport(Collection $assets, Collection $workOrders, Collection $units): string
    {
        $directory = storage_path('app/asset-purges');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.now()->format('Ymd_His').'_deleted_assets.csv';

        $handle = fopen($path, 'w');
        fputcsv($handle, ['eqm_id', 'asset_code', 'asset_name', 'asset_type', 'active', 'reason', 'match', 'deleted_work_orders'], escape: '');

        foreach ($assets as $asset) {
            fputcsv($handle, [
                $asset['id'],
                $units[$asset['id']]->eqm_prc_code,
                $asset['name'],
                $asset['type'],
                $units[$asset['id']]->eqm_is_active ? 'yes' : 'no',
                $asset['reason'],
                $asset['match'],
                $workOrders->where('wo_eqm_id', $asset['id'])->pluck('wo_no')->implode(' '),
            ], escape: '');
        }

        fclose($handle);

        return $path;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\AssetType;
use App\Models\Equipment;
use App\Services\Aims\AimsAssetTagImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportAimsAssets extends Command
{
    protected $signature = 'assets:import-aims
        {source=database/data/aims_assets_to_add.csv : CSV with Asset Code, Asset Name, Asset Type (Equipment, Accessory or Service Vehicle) and, optionally, Remarks (AIMS)}
        {--execute : Really add the assets; without it the command only shows what would be added}
        {--force : Skip the confirmation question (with --execute)}';

    protected $description = 'Add AIMS assets that are missing in EMMS to equipment_units (no allocation to a parent)';

    /** @var array<string, string> asset type as written in the CSV => asset_types.code */
    private const TYPES = [
        'equipment' => AssetType::EQUIPMENT,
        'accessory' => AssetType::ACCESSORY,
        'service vehicle' => AssetType::SERVICE_VEHICLE,
    ];

    public function handle(AimsAssetTagImporter $importer): int
    {
        $source = (string) $this->argument('source');
        $path = File::exists($source) ? $source : base_path($source);

        if (! File::exists($path)) {
            $this->error("File not found: {$source}");

            return self::FAILURE;
        }

        // An asset counts as "already in EMMS" the same way the tag import matches codes (case and spaces ignored).
        $existing = DB::table('equipment_units')->pluck('eqm_prc_code')
            ->mapWithKeys(fn ($code) => [$importer->normalizeCode((string) $code) => (string) $code]);

        $toAdd = [];
        $skipped = [];
        $seen = [];

        foreach ($this->readRows($path) as $line => $row) {
            $code = trim((string) preg_replace('/\s+/u', ' ', $row['Asset Code'] ?? ''));
            $name = trim((string) preg_replace('/\s+/u', ' ', ($row['Asset Name'] ?? '') !== '' ? $row['Asset Name'] : $code));
            $type = self::TYPES[mb_strtolower(trim($row['Asset Type'] ?? ''))] ?? null;
            $key = $importer->normalizeCode($code);

            $reason = match (true) {
                $code === '' => 'no asset code',
                mb_strlen($code) > 30 => 'asset code is longer than 30 characters',
                $type === null => 'unknown asset type "'.($row['Asset Type'] ?? '').'"',
                $existing->has($key) => 'already in EMMS as '.$existing[$key],
                isset($seen[$key]) => 'listed twice in the file',
                default => null,
            };

            if ($reason !== null) {
                $skipped[] = ['line' => $line, 'code' => $code, 'reason' => $reason];

                continue;
            }

            $seen[$key] = true;
            $toAdd[] = [
                'eqm_prc_code' => $code,
                'eqm_name' => mb_substr($name, 0, 255),
                'type' => $type,
                'type_label' => trim($row['Asset Type']),
                'remarks' => trim((string) ($row['Remarks (AIMS)'] ?? '')),
            ];
        }

        $this->table(['Result', 'Count'], [
            ['Rows in '.$source, count($toAdd) + count($skipped)],
            ['To add', count($toAdd)],
            ['Skipped', count($skipped)],
        ]);
        $this->table(['Asset type', 'To add'], collect($toAdd)->countBy('type_label')->map(fn ($n, $t) => [$t, $n])->values()->all());

        foreach (collect($skipped)->countBy('reason') as $reason => $count) {
            $this->line("  skipped {$count}: {$reason}");
        }

        if ($toAdd === []) {
            $this->info('Nothing to add.');

            return self::SUCCESS;
        }

        if (! $this->option('execute')) {
            $this->info('Preview only. Run again with --execute to add them.');

            return self::SUCCESS;
        }

        $missingTypes = collect($toAdd)->pluck('type')->unique()->reject(fn ($code) => AssetType::idFor($code) !== null);

        if ($missingTypes->isNotEmpty()) {
            $this->error('These asset types are missing in asset_types: '.$missingTypes->implode(', '));

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Add '.count($toAdd).' assets to EMMS?', false)) {
            $this->warn('Cancelled. Nothing was added.');

            return self::FAILURE;
        }

        $created = DB::transaction(fn (): array => collect($toAdd)->map(fn (array $asset): array => [
            'id' => Equipment::query()->create([
                'eqm_prc_code' => $asset['eqm_prc_code'],
                'eqm_name' => $asset['eqm_name'],
                'asset_type_id' => AssetType::idFor($asset['type']),
                'eqm_is_active' => true,
                'specifications' => $asset['remarks'] !== '' ? '<p>AIMS remarks: '.e($asset['remarks']).'</p>' : null,
            ])->getKey(),
        ] + $asset)->all());

        $report = $this->writeReport($created);

        activity()
            ->event('created')
            ->withProperties(['assets' => count($created), 'report' => $report])
            ->log('Added '.count($created).' assets from AIMS');

        $this->info('Added '.count($created).' assets. Report: '.$report);

        return self::SUCCESS;
    }

    /**
     * @return array<int, array<string, string>> line number => row
     */
    private function readRows(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = array_map(fn ($h) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), fgetcsv($handle, 0, ',', '"', '') ?: []);
        $rows = [];
        $line = 1;

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $line++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $rows[$line] = array_combine($header, array_pad($row, count($header), ''));
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $created
     */
    private function writeReport(array $created): string
    {
        $directory = storage_path('app/asset-imports');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.now()->format('Ymd_His').'_added_assets.csv';

        $handle = fopen($path, 'w');
        fputcsv($handle, ['eqm_id', 'asset_code', 'asset_name', 'asset_type', 'aims_remarks'], escape: '');

        foreach ($created as $asset) {
            fputcsv($handle, [$asset['id'], $asset['eqm_prc_code'], $asset['eqm_name'], $asset['type_label'], $asset['remarks']], escape: '');
        }

        fclose($handle);

        return $path;
    }
}

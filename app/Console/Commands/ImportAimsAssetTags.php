<?php

namespace App\Console\Commands;

use App\Services\Aims\AimsAssetTagImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportAimsAssetTags extends Command
{
    protected $signature = 'asset-tags:import-aims
        {--dry-run : Show what would be imported without writing anything}
        {--report=aims-asset-tags-skipped.csv : CSV (inside storage/app) listing every skipped tag}';

    protected $description = 'Import RFID tags from the AIMS RFIDTbl table into asset_tags (tag_id, asset_parent_id)';

    public function handle(AimsAssetTagImporter $importer): int
    {
        $aims = DB::connection(env('DB_AIMS_CONNECTION', 'sqlsrv_aims'));

        $tags = $aims->table('RFIDTbl')->select(['ID', 'RFID', 'ASSIGNED_TO'])->orderBy('ID')->get();
        $parents = $aims->table('PARENT')->select(['ID', 'NAME', 'IS_DELETE'])->get();
        $accessories = $aims->table('ACCESSORIES')->select(['ID', 'PARENT_ID', 'IS_DELETE'])->get();

        $unitsByCode = [];

        foreach (DB::table('equipment_units')->whereNotNull('eqm_prc_code')->get(['eqm_id', 'eqm_prc_code']) as $unit) {
            $code = $importer->normalizeCode($unit->eqm_prc_code);

            if ($code !== '') {
                $unitsByCode[$code][] = (int) $unit->eqm_id;
            }
        }

        $plan = $importer->plan($tags, $parents, $accessories, $unitsByCode);

        $existing = DB::table('asset_tags')->pluck('asset_parent_id', 'tag_id')
            ->mapWithKeys(fn ($unitId, $tagId) => [mb_strtolower($tagId) => $unitId]);

        $alreadyPresent = array_intersect_key($plan['rows'], $existing->all());
        $differentUnit = array_filter(
            $alreadyPresent,
            fn (int $unitId, string $tagId) => (int) $existing[$tagId] !== $unitId,
            ARRAY_FILTER_USE_BOTH,
        );
        $toInsert = array_diff_key($plan['rows'], $alreadyPresent);

        $reportPath = storage_path('app/'.ltrim((string) $this->option('report'), '/\\'));
        $this->writeReport($reportPath, $plan['skipped']);

        $this->table(['Result', 'Count'], [
            ['AIMS tags read', $tags->count()],
            ['Mapped to an EMMS unit', count($plan['rows'])],
            ['  of which via an accessory\'s parent', $plan['from_accessories']],
            ['Exact duplicates in AIMS (merged)', $plan['duplicates']],
            ['Already in asset_tags (left untouched)', count($alreadyPresent)],
            ['  of which on a different unit than AIMS suggests', count($differentUnit)],
            ['Will be inserted', count($toInsert)],
            ['Skipped (see report)', count($plan['skipped'])],
        ]);

        collect($plan['skipped'])->countBy('reason')->each(
            fn (int $count, string $reason) => $this->line("  skipped {$count}: {$reason}")
        );

        $this->line("Skipped report: {$reportPath}");

        if ($this->option('dry-run')) {
            $this->info('Dry run - nothing was written.');

            return self::SUCCESS;
        }

        $inserted = $importer->persist($toInsert);
        $this->info("Inserted {$inserted} tag(s) into asset_tags.");

        return self::SUCCESS;
    }

    /**
     * @param  list<array{aims_id: int, rfid: string, assigned_to: int, reason: string}>  $skipped
     */
    private function writeReport(string $path, array $skipped): void
    {
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'w');
        fputcsv($handle, ['aims_id', 'rfid', 'assigned_to', 'reason'], escape: '');

        foreach ($skipped as $row) {
            fputcsv($handle, [$row['aims_id'], $row['rfid'], $row['assigned_to'], $row['reason']], escape: '');
        }

        fclose($handle);
    }
}

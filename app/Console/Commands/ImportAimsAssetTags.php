<?php

namespace App\Console\Commands;

use App\Services\Aims\AimsAssetTagImporter;
use App\Services\Aims\AimsUserMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportAimsAssetTags extends Command
{
    protected $signature = 'asset-tags:import-aims
        {--dry-run : Show what would be imported (and removed) without writing anything}
        {--remove-misplaced : Also remove tags that sit on a parent but belong to an accessory that is not an asset in EMMS (tags with scan logs are never removed)}
        {--aliases=database/data/aims_code_aliases.csv : CSV of AIMS codes that have a different code in EMMS (AIMS Code, EMMS Code)}
        {--assets=database/data/aims_assets_to_add.csv : CSV of AIMS assets added to EMMS (Asset Code, AIMS Table, AIMS ID): their accessories keep their own tags}
        {--report=aims-asset-tags-skipped.csv : CSV (inside storage/app) listing every skipped tag}';

    protected $description = 'Import RFID tags from the AIMS RFIDTbl table into asset_tags, with who assigned / scanned them and when';

    public function handle(AimsAssetTagImporter $importer, AimsUserMapper $userMapper): int
    {
        $aims = DB::connection(env('DB_AIMS_CONNECTION', 'sqlsrv_aims'));

        $tags = $aims->table('RFIDTbl')
            ->select(['ID', 'RFID', 'ASSIGNED_TO', 'ASSIGNED_BY', 'ASSIGNED_DATE', 'SCANNED_BY', 'SCANNED_DATE', 'IS_DELETE'])
            ->orderBy('ID')
            ->get();
        $parents = $aims->table('PARENT')->select(['ID', 'NAME', 'IS_DELETE'])->get();
        $accessories = $aims->table('ACCESSORIES')->select(['ID', 'PARENT_ID', 'NAME', 'IS_DELETE'])->get();

        $unitsByCode = [];
        $unitsByExactCode = [];

        foreach (DB::table('equipment_units')->whereNotNull('eqm_prc_code')->get(['eqm_id', 'eqm_prc_code']) as $unit) {
            $code = $importer->normalizeCode($unit->eqm_prc_code);

            if ($code !== '') {
                $unitsByCode[$code][] = (int) $unit->eqm_id;
                $unitsByExactCode[$importer->exactCode($unit->eqm_prc_code)] = (int) $unit->eqm_id;
            }
        }

        $aliases = collect($this->readCsv((string) $this->option('aliases')))
            ->filter(fn (array $row): bool => trim($row['AIMS Code'] ?? '') !== '' && trim($row['EMMS Code'] ?? '') !== '')
            ->mapWithKeys(fn (array $row): array => [$row['AIMS Code'] => $row['EMMS Code']])
            ->all();

        // Accessories added to EMMS as assets of their own, by the AIMS accessory id they came from.
        $accessoryUnits = collect($this->readCsv((string) $this->option('assets')))
            ->filter(fn (array $row): bool => ($row['AIMS Table'] ?? '') === 'ACCESSORIES' && is_numeric($row['AIMS ID'] ?? null))
            ->mapWithKeys(fn (array $row): array => [(int) $row['AIMS ID'] => $unitsByExactCode[$importer->exactCode((string) preg_replace('/\s+/u', ' ', $row['Asset Code']))] ?? null])
            ->filter()
            ->all();

        $plan = $importer->plan($tags, $parents, $accessories, $unitsByCode, $unitsByExactCode, $aliases, $accessoryUnits);

        $existing = DB::table('asset_tags')->pluck('asset_parent_id', 'tag_id')
            ->mapWithKeys(fn ($unitId, $tagId) => [mb_strtolower($tagId) => $unitId]);

        $alreadyPresent = array_intersect_key($plan['rows'], $existing->all());
        $differentUnit = array_filter(
            $alreadyPresent,
            fn (int $unitId, string $tagId) => (int) $existing[$tagId] !== $unitId,
            ARRAY_FILTER_USE_BOTH,
        );
        $toInsert = array_diff_key($plan['rows'], $alreadyPresent);

        // Tags an earlier run attached to a parent although they belong to an accessory that is not an asset in EMMS.
        $misplaced = collect($plan['skipped'])
            ->where('reason', AimsAssetTagImporter::SKIP_ACCESSORY_NOT_ASSET)
            ->pluck('rfid')
            ->map(fn (string $rfid): string => $importer->normalizeTag($rfid))
            ->unique()
            ->filter(fn (string $tagId): bool => $existing->has($tagId))
            ->values();
        $misplacedWithLogs = $misplaced->chunk(500)
            ->flatMap(fn ($chunk) => DB::table('asset_tag_logs')->whereIn('tag_id', $chunk->all())->distinct()->pluck('tag_id'))
            ->map(fn (string $tagId): string => $importer->normalizeTag($tagId))
            ->unique();
        $toRemove = $misplaced->reject(fn (string $tagId): bool => $misplacedWithLogs->contains($tagId))->values();

        $reportPath = storage_path('app/'.ltrim((string) $this->option('report'), '/\\'));
        $this->writeReport($reportPath, $plan['skipped']);

        $this->table(['Result', 'Count'], [
            ['AIMS tags read', $tags->count()],
            ['Mapped to an EMMS unit', count($plan['rows'])],
            ['  of which on the accessory\'s own EMMS record', $plan['from_accessory_records']],
            ['Aliases read from '.$this->option('aliases'), count($aliases)],
            ['Accessories added as assets (from '.$this->option('assets').')', count($accessoryUnits)],
            ['Exact duplicates in AIMS (merged)', $plan['duplicates']],
            ['Already in asset_tags (left untouched)', count($alreadyPresent)],
            ['  of which on a different unit than AIMS suggests', count($differentUnit)],
            ['Will be inserted', count($toInsert)],
            ['Skipped (see report)', count($plan['skipped'])],
            ['Wrongly on a parent already (accessory tags)', $misplaced->count()],
            ['  of which have scan logs (always kept)', $misplacedWithLogs->count()],
            ['  to remove with --remove-misplaced', $toRemove->count()],
        ]);

        collect($plan['skipped'])->countBy('reason')->each(
            fn (int $count, string $reason) => $this->line("  skipped {$count}: {$reason}")
        );

        $this->line("Skipped report: {$reportPath}");

        // Who assigned / scanned the tags being inserted: AIMS user ids => EMMS app_users ids.
        $referenced = collect($toInsert)->keys()
            ->flatMap(fn (string $tagId): array => [$plan['meta'][$tagId]['assigned_by'] ?? null, $plan['meta'][$tagId]['scanned_by'] ?? null])
            ->filter()
            ->unique()
            ->values()
            ->all();
        $aimsUsers = $aims->table('USERS')->get(['ID', 'FULLNAME', 'EMAIL_ADDRESS']);
        $users = $userMapper->plan($aimsUsers, DB::table('app_users')->get(['user_id', 'user_fname', 'user_lname', 'user_email']), $referenced);
        $aimsNames = $aimsUsers->pluck('FULLNAME', 'ID');
        $tagsPerUser = collect($toInsert)->keys()->countBy(fn (string $tagId) => $plan['meta'][$tagId]['assigned_by'] ?? 0);

        $this->newLine();
        $this->table(['AIMS user', 'AIMS id', 'Tags assigned', 'EMMS user'], collect($referenced)->sort()->map(fn (int $aimsId): array => [
            $aimsNames[$aimsId] ?? '(not in AIMS USERS)',
            $aimsId,
            $tagsPerUser[$aimsId] ?? 0,
            match (true) {
                isset($users['map'][$aimsId]) => 'existing user #'.$users['map'][$aimsId],
                isset($users['create'][$aimsId]) => 'NEW (AIMS dept, inactive): '.trim($users['create'][$aimsId]['user_fname'].' '.$users['create'][$aimsId]['user_lname']).' <'.$users['create'][$aimsId]['user_email'].'>',
                isset($users['ambiguous'][$aimsId]) => 'AMBIGUOUS - several EMMS users have this name, left empty',
                default => 'not found in AIMS USERS - left empty',
            },
        ])->values()->all());

        $departmentId = DB::table('departments')->where('dep_code', 'AIMS')->value('dep_id');

        if ($users['create'] !== [] && $departmentId === null) {
            $this->error('There is no AIMS department (dep_code AIMS) to put the new users in.');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run - nothing was written.');

            return self::SUCCESS;
        }

        $removeMisplaced = (bool) $this->option('remove-misplaced');

        [$created, $inserted, $removed] = DB::transaction(function () use ($userMapper, $importer, $users, $departmentId, $toInsert, $plan, $removeMisplaced, $toRemove): array {
            $created = $userMapper->create($users['create'], (int) $departmentId);
            $inserted = $importer->persist($toInsert, $plan['meta'], $users['map'] + $created);
            $removed = 0;

            // Query builder on purpose: the tags have no logs (checked above) and the foreign key backs that up.
            if ($removeMisplaced) {
                foreach ($toRemove->chunk(500) as $chunk) {
                    $removed += DB::table('asset_tags')->whereIn('tag_id', $chunk->all())->delete();
                }
            }

            return [$created, $inserted, $removed];
        });

        activity()
            ->event('created')
            ->withProperties(['tags_inserted' => $inserted, 'tags_removed' => $removed, 'users_created' => array_values($created), 'skipped' => count($plan['skipped'])])
            ->log("Imported {$inserted} RFID tags from AIMS".($removed > 0 ? " and removed {$removed} that belong to accessories" : ''));

        $this->info('Created '.count($created).' AIMS user(s) in EMMS, inserted '.$inserted.' tag(s) into asset_tags and removed '.$removed.' misplaced tag(s).');

        if (! $removeMisplaced && $toRemove->isNotEmpty()) {
            $this->warn($toRemove->count().' tags still sit on a parent although they belong to accessories. Run again with --remove-misplaced to remove them.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, string>> rows keyed by header; empty when the file does not exist
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

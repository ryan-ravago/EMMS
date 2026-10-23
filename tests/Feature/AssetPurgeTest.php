<?php

namespace Tests\Feature;

use App\Services\Assets\AssetPurger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssetPurgeTest extends TestCase
{
    /** @var list<string> */
    private array $reports = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->string('eqm_prc_code')->nullable();
            $table->string('eqm_name')->nullable();
            $table->unsignedBigInteger('asset_type_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('eqm_is_active')->default(true);
        });

        $tables = [
            'work_orders' => ['wo_id' => 'id', 'wo_no' => 'string', 'wo_eqm_id' => 'int', 'wo_mt_id' => 'nint', 'wo_status_id' => 'nstring'],
            'work_order_logs' => ['wol_id' => 'id', 'wol_wo_id' => 'int'],
            'work_order_log_updates' => ['wolu_id' => 'id', 'wolu_wo_id' => 'int'],
            'work_order_assignments' => ['woa_wo_id' => 'int', 'woa_worker_id' => 'int'],
            'work_order_notes' => ['won_id' => 'id', 'won_wo_id' => 'int'],
            'report_submissions' => ['rs_id' => 'id', 'rs_wo_id' => 'int'],
            'worker_reports' => ['wr_rs_id' => 'int', 'wr_worker_id' => 'int'],
            'maintenance_tasks' => ['mt_id' => 'id', 'mt_eqm_id' => 'int'],
            'maintenance_task_logs' => ['mtl_id' => 'id', 'mtl_mt_id' => 'int', 'mtl_wo_id' => 'nint'],
            'equipment_tasks_schedules' => ['ets_id' => 'id', 'ets_eqm_id' => 'int'],
            'inspections' => ['ins_id' => 'id', 'ins_eqm_id' => 'int'],
            'inspection_items' => ['insi_id' => 'id', 'insi_ins_id' => 'int'],
            'inspection_item_logs' => ['inil_id' => 'id', 'inil_insi_id' => 'int', 'inil_wo_id' => 'nint'],
            'lifecycle_logs' => ['id' => 'id', 'asset_id' => 'int'],
            'asset_edit_logs' => ['id' => 'id', 'asset_id' => 'int'],
            'asset_tags' => ['tag_id' => 'string', 'asset_parent_id' => 'nint'],
            'asset_tag_logs' => ['id' => 'id', 'tag_id' => 'string'],
            'equipment_unit_category' => ['eqm_id' => 'int', 'eqmc_id' => 'int'],
            'equipment_category_equipment' => ['equipment_id' => 'int', 'equipment_category_id' => 'int'],
            'activity_log' => ['id' => 'id', 'subject_type' => 'nstring', 'subject_id' => 'nint', 'description' => 'nstring'],
        ];

        foreach ($tables as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column => $type) {
                    match ($type) {
                        'id' => $table->id($column),
                        'int' => $table->unsignedBigInteger($column),
                        'nint' => $table->unsignedBigInteger($column)->nullable(),
                        'string' => $table->string($column),
                        'nstring' => $table->string($column)->nullable(),
                    };
                }
            });
        }

        DB::table('asset_types')->insert([['id' => 1, 'code' => 'EQM', 'name' => 'Equipment'], ['id' => 2, 'code' => 'ACC', 'name' => 'Accessory']]);
    }

    protected function tearDown(): void
    {
        foreach ($this->reports as $report) {
            File::delete($report);
        }

        parent::tearDown();
    }

    private function asset(string $code, ?int $typeId = null, ?int $parentId = null): int
    {
        return DB::table('equipment_units')->insertGetId([
            'eqm_prc_code' => $code,
            'eqm_name' => "Asset {$code}",
            'asset_type_id' => $typeId,
            'parent_id' => $parentId,
        ]);
    }

    /**
     * An asset with one of everything attached to it.
     *
     * @return array{asset: int, workOrder: int, task: int}
     */
    private function assetWithHistory(string $code, ?int $typeId = null): array
    {
        $asset = $this->asset($code, $typeId);
        $task = DB::table('maintenance_tasks')->insertGetId(['mt_eqm_id' => $asset]);
        $workOrder = DB::table('work_orders')->insertGetId(['wo_no' => "WO-{$code}", 'wo_eqm_id' => $asset, 'wo_mt_id' => $task]);
        $log = DB::table('work_order_logs')->insertGetId(['wol_wo_id' => $workOrder]);
        DB::table('work_order_log_updates')->insert(['wolu_wo_id' => $workOrder]);
        DB::table('work_order_assignments')->insert(['woa_wo_id' => $workOrder, 'woa_worker_id' => 1]);
        DB::table('work_order_notes')->insert(['won_wo_id' => $workOrder]);
        $submission = DB::table('report_submissions')->insertGetId(['rs_wo_id' => $workOrder]);
        DB::table('worker_reports')->insert(['wr_rs_id' => $submission, 'wr_worker_id' => 1]);
        DB::table('maintenance_task_logs')->insert(['mtl_mt_id' => $task, 'mtl_wo_id' => $workOrder]);
        DB::table('equipment_tasks_schedules')->insert(['ets_eqm_id' => $asset]);
        $inspection = DB::table('inspections')->insertGetId(['ins_eqm_id' => $asset]);
        $item = DB::table('inspection_items')->insertGetId(['insi_ins_id' => $inspection]);
        DB::table('inspection_item_logs')->insert(['inil_insi_id' => $item, 'inil_wo_id' => $workOrder]);
        DB::table('lifecycle_logs')->insert(['asset_id' => $asset]);
        DB::table('asset_edit_logs')->insert(['asset_id' => $asset]);
        DB::table('asset_tags')->insert(['tag_id' => "TAG-{$code}", 'asset_parent_id' => $asset]);
        DB::table('asset_tag_logs')->insert(['tag_id' => "TAG-{$code}"]);
        DB::table('equipment_unit_category')->insert(['eqm_id' => $asset, 'eqmc_id' => 1]);
        DB::table('equipment_category_equipment')->insert(['equipment_id' => $asset, 'equipment_category_id' => 1]);
        DB::table('activity_log')->insert([
            ['subject_type' => 'App\Models\Equipment', 'subject_id' => $asset, 'description' => 'asset'],
            ['subject_type' => 'App\Models\WorkOrder', 'subject_id' => $workOrder, 'description' => 'work order'],
            ['subject_type' => 'App\Models\WorkOrderLog', 'subject_id' => $log, 'description' => 'work order log'],
        ]);

        return ['asset' => $asset, 'workOrder' => $workOrder, 'task' => $task];
    }

    /**
     * @return array<string, int>
     */
    private function rowCounts(): array
    {
        return collect(['equipment_units', 'work_orders', 'work_order_logs', 'work_order_log_updates', 'work_order_assignments', 'work_order_notes', 'report_submissions', 'worker_reports', 'maintenance_tasks', 'maintenance_task_logs', 'equipment_tasks_schedules', 'inspections', 'inspection_items', 'inspection_item_logs', 'lifecycle_logs', 'asset_edit_logs', 'asset_tags', 'asset_tag_logs', 'equipment_unit_category', 'equipment_category_equipment', 'activity_log'])
            ->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->count()])
            ->all();
    }

    private function csv(string $content): string
    {
        $path = storage_path('framework/testing/purge_'.uniqid().'.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        $this->reports[] = $path;

        return $path;
    }

    private function trackReports(): void
    {
        $this->reports = array_merge($this->reports, File::glob(storage_path('app/asset-purges/*_deleted_assets.csv')));
    }

    public function test_codes_are_matched_as_text_so_a_numeric_code_never_matches_a_longer_one(): void
    {
        $this->asset('70DF#1');
        $seventy = $this->asset('70');

        $match = (new AssetPurger)->matchCodes(['70', '50'], DB::table('equipment_units')->get());

        $this->assertSame([70 => $seventy], $match['exact']);
        $this->assertSame(['50'], $match['missing']);
    }

    public function test_spacing_differences_match_only_when_they_point_at_one_asset(): void
    {
        $lb = $this->asset('LB  01');
        $this->asset('MD  01');
        $this->asset('MD   01');

        $match = (new AssetPurger)->matchCodes(['LB 01', 'MD 01', 'lb 01x'], DB::table('equipment_units')->get());

        $this->assertSame(['LB 01' => ['id' => $lb, 'code' => 'LB  01']], $match['spacing']);
        $this->assertEqualsCanonicalizing(['MD  01', 'MD   01'], $match['ambiguous']['MD 01']);
        $this->assertSame(['lb 01x'], $match['missing']);
    }

    public function test_purging_removes_the_asset_and_everything_related_and_nothing_else(): void
    {
        $doomed = $this->assetWithHistory('GONE');
        $kept = $this->assetWithHistory('KEEP');
        $before = $this->rowCounts();

        $deleted = (new AssetPurger)->purge([$doomed['asset']]);

        $after = $this->rowCounts();

        // One row of everything went with the asset; the activity log had three rows about it.
        foreach ($before as $table => $count) {
            $this->assertSame($table === 'activity_log' ? $count - 3 : $count - 1, $after[$table], $table);
        }

        $this->assertSame(1, $deleted['Assets']);
        $this->assertTrue(DB::table('equipment_units')->where('eqm_id', $kept['asset'])->exists());
        $this->assertTrue(DB::table('work_orders')->where('wo_id', $kept['workOrder'])->exists());
        $this->assertSame(0, DB::table('asset_tag_logs')->where('tag_id', 'TAG-GONE')->count());
        $this->assertSame(1, DB::table('asset_tag_logs')->where('tag_id', 'TAG-KEEP')->count());
    }

    public function test_rows_that_belong_to_something_else_are_detached_not_deleted(): void
    {
        $doomed = $this->assetWithHistory('GONE');
        $other = $this->asset('OTHER');
        $accessory = $this->asset('ACC-1', 2, $doomed['asset']);
        $otherWorkOrder = DB::table('work_orders')->insertGetId(['wo_no' => 'WO-OTHER', 'wo_eqm_id' => $other, 'wo_mt_id' => $doomed['task']]);
        $foreignLog = DB::table('maintenance_task_logs')->insertGetId(['mtl_mt_id' => DB::table('maintenance_tasks')->insertGetId(['mt_eqm_id' => $other]), 'mtl_wo_id' => $doomed['workOrder']]);

        (new AssetPurger)->purge([$doomed['asset']]);

        $this->assertNull(DB::table('equipment_units')->where('eqm_id', $accessory)->value('parent_id'));
        $this->assertNull(DB::table('work_orders')->where('wo_id', $otherWorkOrder)->value('wo_mt_id'));
        $this->assertNull(DB::table('maintenance_task_logs')->where('mtl_id', $foreignLog)->value('mtl_wo_id'));
    }

    public function test_the_command_only_previews_without_execute(): void
    {
        $this->assetWithHistory('GONE');
        $before = $this->rowCounts();

        $this->artisan('assets:purge', ['source' => $this->csv("Asset Code,Reason to Delete\nGONE,Location\n")])
            ->expectsOutputToContain('Preview only')
            ->assertSuccessful();

        $this->assertSame($before, $this->rowCounts());
    }

    public function test_the_command_deletes_untyped_assets_and_keeps_typed_ones_by_default(): void
    {
        $untyped = $this->assetWithHistory('GONE');
        $typed = $this->assetWithHistory('TRUCK', 1);
        $unlisted = $this->asset('STAYS');

        $this->artisan('assets:purge', ['source' => $this->csv("Asset Code,Reason to Delete\nGONE,Location\nTRUCK,Generic\nNOPE,Generic\n"), '--execute' => true, '--force' => true])
            ->expectsOutputToContain('are NOT deleted')
            ->expectsOutputToContain('Not found: NOPE')
            ->assertSuccessful();
        $this->trackReports();

        $this->assertFalse(DB::table('equipment_units')->where('eqm_id', $untyped['asset'])->exists());
        $this->assertFalse(DB::table('work_orders')->where('wo_id', $untyped['workOrder'])->exists());
        $this->assertTrue(DB::table('equipment_units')->where('eqm_id', $typed['asset'])->exists());
        $this->assertTrue(DB::table('work_orders')->where('wo_id', $typed['workOrder'])->exists());
        $this->assertTrue(DB::table('equipment_units')->where('eqm_id', $unlisted)->exists());

        $report = collect($this->reports)->first(fn (string $path): bool => str_contains($path, 'asset-purges'));
        $this->assertNotNull($report);
        $this->assertStringContainsString('GONE', File::get($report));
        $this->assertStringContainsString('WO-GONE', File::get($report));
        $this->assertStringNotContainsString('TRUCK', File::get($report));
    }

    public function test_include_typed_also_deletes_assets_that_have_an_asset_type(): void
    {
        $typed = $this->assetWithHistory('TRUCK', 1);

        $this->artisan('assets:purge', ['source' => $this->csv("Asset Code\nTRUCK\n"), '--execute' => true, '--force' => true, '--include-typed' => true])
            ->assertSuccessful();
        $this->trackReports();

        $this->assertFalse(DB::table('equipment_units')->where('eqm_id', $typed['asset'])->exists());
    }

    public function test_declining_the_confirmation_deletes_nothing(): void
    {
        $this->assetWithHistory('GONE');
        $before = $this->rowCounts();

        $this->artisan('assets:purge', ['source' => $this->csv("Asset Code\nGONE\n"), '--execute' => true])
            ->expectsConfirmation('Permanently delete 1 assets and all of their related data? This cannot be undone.', 'no')
            ->assertFailed();

        $this->assertSame($before, $this->rowCounts());
    }

    public function test_a_google_sheets_link_is_downloaded_as_csv(): void
    {
        $this->asset('GONE');

        Http::fake(['docs.google.com/spreadsheets/d/abc123/export?format=csv&gid=0' => Http::response("Asset Code\nGONE\n", 200, ['Content-Type' => 'text/csv'])]);

        $this->artisan('assets:purge', ['source' => 'https://docs.google.com/spreadsheets/d/abc123/edit?usp=sharing'])
            ->expectsOutputToContain('Preview only')
            ->assertSuccessful();

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/spreadsheets/d/abc123/export?format=csv&gid=0'));
    }

    public function test_without_a_source_the_list_kept_in_the_project_is_used(): void
    {
        $this->assertFileExists(base_path('database/data/assets_to_delete.csv'));

        $this->asset('1010');

        $this->artisan('assets:purge')
            ->expectsOutputToContain('Codes in the sheet: 551')
            ->expectsOutputToContain('Preview only')
            ->assertSuccessful();
    }

    public function test_a_missing_code_column_stops_the_command(): void
    {
        $this->artisan('assets:purge', ['source' => $this->csv("Code\nGONE\n")])
            ->expectsOutputToContain('Column "Asset Code" not found')
            ->assertFailed();
    }
}

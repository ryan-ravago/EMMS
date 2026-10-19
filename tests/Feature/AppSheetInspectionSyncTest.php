<?php

namespace Tests\Feature;

use App\Models\AimsInspection;
use App\Models\MaintenanceInspection;
use App\Models\SyncAuditLog;
use App\Services\Google\GoogleSheetReader;
use App\Services\Inspections\AppSheetInspectionSync as Sync;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class AppSheetInspectionSyncTest extends TestCase
{
    private Sync $sync;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code');
            $table->string('dep_name');
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->string('eqm_name')->nullable();
            $table->string('eqm_prc_code')->nullable();
        });

        Schema::create('sync_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('sheet_name');
            $table->integer('synced_rows')->default(0);
            $table->integer('last_row_processed')->nullable();
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();
        });

        (require base_path('database/migrations/2026_10_19_162850_create_maintenance_and_aims_inspection_tables.php'))->up();

        DB::table('departments')->insert([
            ['dep_code' => 'MECH', 'dep_name' => 'Mechanical'],
            ['dep_code' => 'AIMS', 'dep_name' => 'Asset Inventory Management'],
        ]);
        DB::table('equipment_units')->insert([
            ['eqm_id' => 5, 'eqm_name' => '10 Wheeler Truck #1', 'eqm_prc_code' => '10w#1'],
            ['eqm_id' => 6, 'eqm_name' => '10 Wheeler Truck #2', 'eqm_prc_code' => '10w#2'],
        ]);

        $this->sync = new Sync;
    }

    /**
     * @param  list<string>  $columns
     * @param  array<string, string|null>  $values
     * @return array<string, string|null>
     */
    private function row(array $columns, array $values): array
    {
        return array_merge(array_fill_keys($columns, null), $values);
    }

    /**
     * @return array<string, mixed>
     */
    private function lookups(): array
    {
        return $this->sync->buildLookups(
            [
                ['status_id' => 'clrd', 'status' => 'Completed'],
                ['status_id' => 'cncld', 'status' => 'Cancelled'],
                ['status_id' => 'pim', 'status' => 'Pending - Manager Approval'],
            ],
            [
                ['a_id' => 'ap', 'a_past_tense' => 'Approved'],
                ['a_id' => 'arj', 'a_past_tense' => 'Rejected'],
                ['a_id' => 'asub', 'a_past_tense' => 'Submitted'],
            ],
            [['chk_id' => '4413bead', 'chk_name' => 'Checklist 1']],
            $this->sync->departmentLookup(),
            $this->sync->equipmentLookup([5, 6, 999]),
        );
    }

    private function maintenanceRow(array $values = []): array
    {
        return $this->row(Sync::MAINTENANCE_COLUMNS, $values + [
            'insp_id' => '77bb74cc',
            'insp_no' => 'IN-ME-261002001',
            'insp_status_id' => 'clrd',
            'insp_dep_id' => 'MECH',
            'insp_eqm_id' => '5',
            'insp_checklist_temp_id' => '4413bead',
            'insp_req_by' => 'janica.murillo@ravago.com.ph',
            'insp_req_at' => '10/2/2026 11:52:40',
            'insp_manager_action_id' => 'ap',
            'insp_manager_action_by' => 'alain.evangelista@ravago.com.ph',
            'insp_manager_technicians' => 'richard.vea@ravago.com.ph , laav.blanche@gmail.com , tech1@g.co',
            'insp_manager_verifiers' => 'verifier1@g.co , verifier2@g.co',
            'insp_tech_action_id' => 'asub',
            'insp_tech_action_by' => 'richard.vea@ravago.com.ph',
            'insp_verifier_action_id' => 'ap',
            'insp_verifier_action_by' => 'verifier1@g.co',
            'insp_verifier_remarks' => 'hh',
        ]);
    }

    private function maintenanceLog(string $inspectionId, string $action, string $status, string $at, ?string $by = 'someone@g.co'): array
    {
        return $this->row(Sync::MAINTENANCE_LOG_COLUMNS, [
            'inspl_insp_id' => $inspectionId,
            'inspl_last_action' => $action,
            'inspl_status' => $status,
            'inspl_by' => $by,
            'inspl_ts' => $at,
        ]);
    }

    private function aimsRow(array $values = []): array
    {
        return $this->row(Sync::AIMS_COLUMNS, $values + [
            'ainsp_id' => 'e741733a',
            'ainsp_no' => 'IN-AI-261002001',
            'ainsp_status_id' => 'clrd',
            'ainsp_eqm_id' => '6',
            'ainsp_checklist_temp_name' => 'Template 2',
            'ainsp_lessee_name' => 'Samsung',
            'ainsp_jobsite_name' => 'Laguna',
            'ainsp_operator_name' => 'Boltron',
            'ainsp_day_duration' => '3',
            'ainsp_by' => 'fch',
            'ainsp_dt' => '10/2/2026 9:09:00',
            'ainsp_req_by' => 'janica.murillo@ravago.com.ph',
            'ainsp_req_dt' => '10/2/2026 9:09:57',
            'ainsp_remarks2' => 'jk',
        ]);
    }

    public function test_only_closed_maintenance_inspections_are_planned(): void
    {
        $planned = $this->sync->planMaintenance([
            $this->maintenanceRow(['insp_id' => 'a', 'insp_no' => 'IN-1', 'insp_status_id' => 'clrd']),
            $this->maintenanceRow(['insp_id' => 'b', 'insp_no' => 'IN-2', 'insp_status_id' => 'cncld']),
            $this->maintenanceRow(['insp_id' => 'c', 'insp_no' => 'IN-3', 'insp_status_id' => 'pim']),
            $this->maintenanceRow(['insp_id' => 'd', 'insp_no' => 'IN-4', 'insp_status_id' => 'rej']),
            $this->maintenanceRow(['insp_id' => 'e', 'insp_no' => 'IN-5', 'insp_status_id' => null]),
        ], [], [], $this->lookups());

        $this->assertSame(['IN-1', 'IN-2'], array_column($planned['plans'], 'inspection_no'));
        $this->assertSame(['Completed', 'Cancelled'], array_column(array_column($planned['plans'], 'attributes'), 'status'));
        $this->assertSame(3, $planned['open']);
    }

    public function test_codes_are_replaced_by_text(): void
    {
        $attributes = $this->sync->planMaintenance([$this->maintenanceRow()], [], [], $this->lookups())['plans'][0]['attributes'];

        $this->assertSame('MECH', $attributes['department_code']);
        $this->assertSame('Mechanical', $attributes['department_name']);
        $this->assertSame('10w#1', $attributes['equipment_prc_code']);
        $this->assertSame('10 Wheeler Truck #1', $attributes['equipment_name']);
        $this->assertSame('Checklist 1', $attributes['checklist_name']);
        $this->assertSame('Approved', $attributes['manager_action']);
        $this->assertSame('Submitted', $attributes['tech_action']);
        $this->assertSame('Approved', $attributes['verifier_action']);
        $this->assertSame('2026-10-02 11:52:40', $attributes['requested_at']);
        $this->assertSame('richard.vea@ravago.com.ph, laav.blanche@gmail.com, tech1@g.co', $attributes['technicians']);
        $this->assertSame('verifier1@g.co, verifier2@g.co', $attributes['verifiers']);
    }

    public function test_items_and_logs_are_attached_to_their_inspection_and_logs_are_sorted(): void
    {
        $planned = $this->sync->planMaintenance(
            [$this->maintenanceRow(['insp_id' => 'a', 'insp_no' => 'IN-1']), $this->maintenanceRow(['insp_id' => 'b', 'insp_no' => 'IN-2'])],
            [
                $this->row(Sync::MAINTENANCE_ITEM_COLUMNS, ['inspi_insp_id' => 'a', 'inspi_task' => 'Lights', 'inspi_result' => 'passed']),
                $this->row(Sync::MAINTENANCE_ITEM_COLUMNS, ['inspi_insp_id' => 'a', 'inspi_task' => 'Horn', 'inspi_result' => 'n/a']),
                $this->row(Sync::MAINTENANCE_ITEM_COLUMNS, ['inspi_insp_id' => 'a', 'inspi_remarks' => 'orphan remark']),
                $this->row(Sync::MAINTENANCE_ITEM_COLUMNS, ['inspi_insp_id' => 'b', 'inspi_task' => 'Brakes', 'inspi_result' => 'Failed']),
            ],
            [
                $this->maintenanceLog('a', 'Approved', 'Pending - Tech Submission', '10/2/2026 12:00:13'),
                $this->maintenanceLog('a', 'Requested', 'Pending - Manager Approval', '10/2/2026 11:52:40'),
                $this->maintenanceLog('b', 'Requested', 'Pending - Manager Approval', '10/5/2026 9:58:13'),
            ],
            $this->lookups(),
        )['plans'];

        $this->assertSame([['Lights', 'Passed'], ['Horn', 'N/A']], array_map(fn (array $item): array => [$item['task'], $item['result']], $planned[0]['items']));
        $this->assertSame(['Requested', 'Approved'], array_column($planned[0]['logs'], 'action'));
        $this->assertSame('2026-10-02 12:00:13', $planned[0]['attributes']['closed_at']);
        $this->assertSame([['Brakes', 'Failed']], array_map(fn (array $item): array => [$item['task'], $item['result']], $planned[1]['items']));
        $this->assertSame('2026-10-05 09:58:13', $planned[1]['attributes']['closed_at']);
    }

    public function test_unknown_equipment_is_kept_with_empty_text_and_counted(): void
    {
        $planned = $this->sync->planMaintenance([$this->maintenanceRow(['insp_eqm_id' => '999'])], [], [], $this->lookups());

        $this->assertSame(1, $planned['unresolved_equipment']);
        $this->assertNull($planned['plans'][0]['attributes']['equipment_prc_code']);
        $this->assertNull($planned['plans'][0]['attributes']['equipment_name']);
    }

    public function test_inspections_without_a_number_or_with_a_repeated_number_are_invalid(): void
    {
        $planned = $this->sync->planMaintenance([
            $this->maintenanceRow(['insp_id' => 'a', 'insp_no' => 'IN-1']),
            $this->maintenanceRow(['insp_id' => 'b', 'insp_no' => 'IN-1']),
            $this->maintenanceRow(['insp_id' => 'c', 'insp_no' => null]),
        ], [], [], $this->lookups());

        $this->assertCount(1, $planned['plans']);
        $this->assertSame(2, $planned['invalid']);
    }

    public function test_cancelled_status_text_falls_back_when_the_statuses_tab_has_no_entry(): void
    {
        $lookups = $this->lookups();
        $lookups['statuses'] = [];

        $planned = $this->sync->planMaintenance([$this->maintenanceRow(['insp_status_id' => 'cncld'])], [], [], $lookups);

        $this->assertSame('Cancelled', $planned['plans'][0]['attributes']['status']);
    }

    public function test_aims_inspection_is_planned_as_text(): void
    {
        $planned = $this->sync->planAims(
            [$this->aimsRow(), $this->aimsRow(['ainsp_id' => 'x', 'ainsp_no' => 'IN-AI-OPEN', 'ainsp_status_id' => 'pi'])],
            [$this->row(Sync::AIMS_ITEM_COLUMNS, ['ainspi_insp_id' => 'e741733a', 'ainspi_task' => 'Brakes', 'ainspi_result' => 'Passed'])],
            [
                $this->row(Sync::AIMS_LOG_COLUMNS, ['ainspl_insp_id' => 'e741733a', 'ainspl_last_action' => 'Created Pre-Operational Inspection', 'ainspl_status' => 'Pending - Inspection', 'ainspl_by' => 'janica.murillo@ravago.com.ph', 'ainspl_ts' => '10/2/2026 9:09:57']),
                $this->row(Sync::AIMS_LOG_COLUMNS, ['ainspl_insp_id' => 'e741733a', 'ainspl_last_action' => 'Submitted', 'ainspl_status' => 'Completed', 'ainspl_remarks' => 'jk', 'ainspl_by' => 'janica.murillo@ravago.com.ph', 'ainspl_ts' => '10/2/2026 9:10:30']),
            ],
            $this->lookups(),
        );

        $this->assertCount(1, $planned['plans']);
        $this->assertSame(1, $planned['open']);

        $attributes = $planned['plans'][0]['attributes'];
        $this->assertSame('Completed', $attributes['status']);
        $this->assertSame('10w#2', $attributes['equipment_prc_code']);
        $this->assertSame('Samsung', $attributes['lessee']);
        $this->assertSame('Laguna', $attributes['jobsite']);
        $this->assertSame('Boltron', $attributes['operator']);
        $this->assertSame(3, $attributes['day_duration']);
        $this->assertSame('2026-10-02 09:09:00', $attributes['inspected_at']);
        $this->assertSame('jk', $attributes['completion_remarks']);
        $this->assertSame('2026-10-02 09:10:30', $attributes['closed_at']);
        $this->assertCount(2, $planned['plans'][0]['logs']);
        $this->assertCount(1, $planned['plans'][0]['items']);
    }

    public function test_persist_writes_header_items_and_logs_and_a_second_run_changes_nothing(): void
    {
        $planned = $this->sync->planMaintenance(
            [$this->maintenanceRow()],
            [$this->row(Sync::MAINTENANCE_ITEM_COLUMNS, ['inspi_insp_id' => '77bb74cc', 'inspi_task' => 'Lights', 'inspi_result' => 'Passed'])],
            [$this->maintenanceLog('77bb74cc', 'Requested', 'Pending - Manager Approval', '10/2/2026 11:52:40')],
            $this->lookups(),
        )['plans'];

        $first = $this->sync->persistMaintenance($planned);
        $second = $this->sync->persistMaintenance($planned);

        $this->assertSame(['inserted' => 1, 'skipped' => 0, 'failed' => 0], $first);
        $this->assertSame(['inserted' => 0, 'skipped' => 1, 'failed' => 0], $second);

        $inspection = MaintenanceInspection::query()->sole();
        $this->assertSame('Mechanical', $inspection->department_name);
        $this->assertCount(1, $inspection->items);
        $this->assertCount(1, $inspection->logs);
        $this->assertSame('Lights', $inspection->items->first()->task);
    }

    public function test_an_existing_inspection_is_never_overwritten(): void
    {
        MaintenanceInspection::factory()->create(['inspection_no' => 'IN-ME-261002001', 'tech_remarks' => 'kept as is']);

        $planned = $this->sync->planMaintenance([$this->maintenanceRow(['insp_tech_remarks' => 'changed in AppSheet'])], [], [], $this->lookups())['plans'];

        $this->assertSame([], $this->sync->onlyNew(MaintenanceInspection::class, $planned));
        $this->sync->persistMaintenance($planned);

        $this->assertSame('kept as is', MaintenanceInspection::query()->sole()->tech_remarks);
    }

    public function test_a_failing_inspection_is_rolled_back_and_does_not_stop_the_others(): void
    {
        $plans = $this->sync->planMaintenance([
            $this->maintenanceRow(['insp_id' => 'a', 'insp_no' => 'IN-BAD']),
            $this->maintenanceRow(['insp_id' => 'b', 'insp_no' => 'IN-GOOD']),
        ], [$this->row(Sync::MAINTENANCE_ITEM_COLUMNS, ['inspi_insp_id' => 'a', 'inspi_task' => 'Lights'])], [], $this->lookups())['plans'];

        // An item row with an unknown column fails after the header was created, so the header must roll back.
        $plans[0]['items'][0]['no_such_column'] = 'x';

        $result = $this->sync->persistMaintenance($plans);

        $this->assertSame(['inserted' => 1, 'skipped' => 0, 'failed' => 1], $result);
        $this->assertSame(['IN-GOOD'], MaintenanceInspection::query()->pluck('inspection_no')->all());
        $this->assertSame(0, DB::table('maintenance_inspection_items')->count());
    }

    public function test_sheet_dates_use_month_day_year_with_24_hour_time(): void
    {
        $this->assertSame('2026-10-02 09:09:57', Sync::parseSheetDate('10/2/2026 9:09:57')?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-12 14:39:55', Sync::parseSheetDate('10/12/2026 14:39:55')?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-19 08:20:00', Sync::parseSheetDate('10/19/2026 8:20')?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-02 00:00:00', Sync::parseSheetDate('10/2/2026')?->format('Y-m-d H:i:s'));
    }

    public function test_unreadable_sheet_dates_become_null(): void
    {
        foreach ([null, '', '   ', 'garbage', '13/45/2026 10:00:00', '2026-10-02 09:09:57', '2/30/2026'] as $value) {
            $this->assertNull(Sync::parseSheetDate($value), var_export($value, true));
        }
    }

    public function test_reader_maps_columns_by_header_ignores_case_and_drops_empty_rows(): void
    {
        $rows = GoogleSheetReader::mapRows('insps', [
            ['INSP_ID', 'x_other', 'Insp_No'],
            ['a1', 'ignored', ' IN-1 '],
            ['', '', ''],
            ['a2'],
            [null, 'only other column', null],
        ], ['insp_id', 'insp_no']);

        $this->assertSame([
            ['insp_id' => 'a1', 'insp_no' => 'IN-1'],
            ['insp_id' => 'a2', 'insp_no' => null],
        ], $rows);
    }

    public function test_reader_rejects_a_sheet_with_missing_columns(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing expected columns in sheet "insps": insp_no');

        GoogleSheetReader::mapRows('insps', [['insp_id']], ['insp_id', 'insp_no']);
    }

    /**
     * @return array<string, list<array<string, string|null>>>
     */
    private function fakeSheets(): array
    {
        return [
            'insps' => [$this->maintenanceRow(['insp_id' => 'a', 'insp_no' => 'IN-1']), $this->maintenanceRow(['insp_id' => 'b', 'insp_no' => 'IN-2', 'insp_status_id' => 'pim'])],
            'insp_items' => [$this->row(Sync::MAINTENANCE_ITEM_COLUMNS, ['inspi_insp_id' => 'a', 'inspi_task' => 'Lights', 'inspi_result' => 'Passed'])],
            'insp_logs' => [$this->maintenanceLog('a', 'Requested', 'Pending - Manager Approval', '10/2/2026 11:52:40')],
            'ainsps' => [$this->aimsRow()],
            'ainsp_items' => [],
            'ainsp_logs' => [],
            'statuses' => [['status_id' => 'clrd', 'status' => 'Completed']],
            'actions' => [['a_id' => 'ap', 'a_past_tense' => 'Approved']],
            'checklists' => [['chk_id' => '4413bead', 'chk_name' => 'Checklist 1']],
        ];
    }

    private function bindFakeSheets(): void
    {
        $sheets = $this->fakeSheets();

        $this->app->instance(GoogleSheetReader::class, new class($sheets) extends GoogleSheetReader
        {
            /**
             * @param  array<string, list<array<string, string|null>>>  $sheets
             */
            public function __construct(private array $sheets) {}

            public function rows(string $sheetName, array $columns): array
            {
                return $this->sheets[$sheetName] ?? throw new RuntimeException("Unknown sheet {$sheetName}");
            }
        });
    }

    public function test_dry_run_reports_counts_and_writes_nothing(): void
    {
        $this->bindFakeSheets();

        $this->artisan('inspections:sync', ['--dry-run' => true])
            ->expectsOutputToContain('Dry run - nothing was written.')
            ->assertSuccessful();

        $this->assertSame(0, MaintenanceInspection::query()->count());
        $this->assertSame(0, AimsInspection::query()->count());
        $this->assertSame(0, SyncAuditLog::query()->count());
    }

    public function test_command_imports_closed_inspections_and_records_an_audit_row_per_sheet(): void
    {
        $this->bindFakeSheets();

        $this->artisan('inspections:sync')->expectsOutputToContain('Sync complete.')->assertSuccessful();

        $this->assertSame(['IN-1'], MaintenanceInspection::query()->pluck('inspection_no')->all());
        $this->assertSame(['IN-AI-261002001'], AimsInspection::query()->pluck('inspection_no')->all());
        $this->assertSame(1, DB::table('maintenance_inspection_items')->count());
        $this->assertEqualsCanonicalizing(['insps', 'ainsps'], SyncAuditLog::query()->pluck('sheet_name')->all());
        $this->assertSame(1, (int) SyncAuditLog::query()->where('sheet_name', 'insps')->value('synced_rows'));

        $this->artisan('inspections:sync')->assertSuccessful();

        $this->assertSame(1, MaintenanceInspection::query()->count());
        $this->assertSame(0, (int) SyncAuditLog::query()->where('sheet_name', 'insps')->latest('id')->value('synced_rows'));
    }

    public function test_command_fails_and_logs_when_the_sheet_cannot_be_read(): void
    {
        $this->app->instance(GoogleSheetReader::class, new class extends GoogleSheetReader
        {
            public function rows(string $sheetName, array $columns): array
            {
                throw new RuntimeException('Google is down');
            }
        });

        $this->artisan('inspections:sync')->expectsOutputToContain('Sync failed: Google is down')->assertFailed();

        $this->assertSame(['failed', 'failed'], SyncAuditLog::query()->pluck('status')->all());
        $this->assertSame(0, MaintenanceInspection::query()->count());
    }
}

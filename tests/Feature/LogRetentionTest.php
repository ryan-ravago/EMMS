<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AimsInspection;
use App\Models\AimsInspectionLog;
use App\Models\MaintenanceInspection;
use App\Models\MaintenanceInspectionLog;
use App\Models\SyncAuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Tests\TestCase;

class LogRetentionTest extends TestCase
{
    private const NOW = '2026-10-20 12:00:00';

    private const MODELS = [Activity::class, MaintenanceInspectionLog::class, AimsInspectionLog::class];

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);
        $this->travelTo(CarbonImmutable::parse(self::NOW));

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
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
        (require base_path('database/migrations/2026_10_20_105530_add_retention_indexes_to_log_tables.php'))->up();
    }

    /**
     * @param  list<class-string>  $models
     */
    private function prune(array $models = self::MODELS, int $chunk = 1000): void
    {
        $this->artisan('model:prune', ['--model' => $models, '--chunk' => $chunk])->assertSuccessful();
    }

    private function activity(string $description, string $createdAt, ?string $task = null): int
    {
        return DB::table('activity_log')->insertGetId([
            'log_name' => 'Sync',
            'description' => $description,
            'properties' => json_encode($task ? ['task' => $task] : []),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * @return list<string> descriptions still stored, oldest first
     */
    private function remainingActivity(): array
    {
        return DB::table('activity_log')->orderBy('created_at')->pluck('description')->all();
    }

    public function test_activity_logs_older_than_30_days_are_deleted_and_newer_ones_are_kept(): void
    {
        $this->activity('40 days old', '2026-09-10 12:00:00');
        $this->activity('31 days old', '2026-09-19 12:00:00');
        $this->activity('30 days and 1 second old', '2026-09-20 11:59:59');
        $this->activity('exactly 30 days old', '2026-09-20 12:00:00');
        $this->activity('29 days old', '2026-09-21 12:00:00');
        $this->activity('today', '2026-10-20 11:00:00');

        $this->prune();

        $this->assertSame(['exactly 30 days old', '29 days old', 'today'], $this->remainingActivity());
    }

    public function test_sap_and_inspection_sync_history_in_the_activity_log_is_pruned_too(): void
    {
        $this->activity('old SAP sync', '2026-08-01 01:00:00', 'SAP equipment sync');
        $this->activity('old maintenance sync', '2026-08-01 01:00:00', 'Maintenance inspection sync');
        $this->activity('old AIMS sync', '2026-08-01 01:00:00', 'AIMS inspection sync');
        $this->activity('recent SAP sync', '2026-10-19 01:00:00', 'SAP equipment sync');

        $this->prune();

        $this->assertSame(['recent SAP sync'], $this->remainingActivity());
    }

    public function test_inspection_logs_are_pruned_by_logged_at(): void
    {
        $inspection = MaintenanceInspection::factory()->create();
        $dates = [
            'old' => '2026-09-01 08:00:00',
            'just over' => '2026-09-20 11:59:59',
            'exactly 30 days' => '2026-09-20 12:00:00',
            'recent' => '2026-10-19 08:00:00',
            'undated' => null,
        ];

        foreach ($dates as $action => $loggedAt) {
            MaintenanceInspectionLog::factory()->create(['maintenance_inspection_id' => $inspection->id, 'action' => $action, 'logged_at' => $loggedAt]);
        }

        $this->prune();

        $this->assertEqualsCanonicalizing(
            ['exactly 30 days', 'recent', 'undated'],
            MaintenanceInspectionLog::query()->pluck('action')->all(),
        );
        $this->assertTrue(MaintenanceInspection::query()->whereKey($inspection->id)->exists(), 'the inspection itself is never pruned');
    }

    public function test_aims_inspection_logs_are_pruned_by_logged_at(): void
    {
        $inspection = AimsInspection::factory()->create();

        foreach (['old' => '2026-07-01 08:00:00', 'just over' => '2026-09-20 11:59:59', 'exactly 30 days' => '2026-09-20 12:00:00', 'recent' => '2026-10-19 08:00:00'] as $action => $loggedAt) {
            AimsInspectionLog::factory()->create(['aims_inspection_id' => $inspection->id, 'action' => $action, 'logged_at' => $loggedAt]);
        }

        $this->prune();

        $this->assertEqualsCanonicalizing(['exactly 30 days', 'recent'], AimsInspectionLog::query()->pluck('action')->all());
        $this->assertTrue(AimsInspection::query()->whereKey($inspection->id)->exists());
    }

    public function test_no_other_table_is_touched(): void
    {
        $oldInspection = MaintenanceInspection::factory()->create(['requested_at' => '2026-01-01 00:00:00', 'closed_at' => '2026-01-02 00:00:00']);
        $oldAims = AimsInspection::factory()->create(['requested_at' => '2026-01-01 00:00:00', 'closed_at' => '2026-01-02 00:00:00']);
        SyncAuditLog::query()->create(['sheet_name' => 'insps', 'synced_rows' => 1, 'status' => 'success', 'synced_at' => '2026-01-01 01:00:00']);
        DB::table('sync_audit_logs')->update(['created_at' => '2026-01-01 01:00:00']);

        $this->prune();

        $this->assertTrue(MaintenanceInspection::query()->whereKey($oldInspection->id)->exists());
        $this->assertTrue(AimsInspection::query()->whereKey($oldAims->id)->exists());
        $this->assertSame(1, SyncAuditLog::query()->count());
    }

    public function test_deletion_happens_in_chunks_until_every_old_row_is_gone(): void
    {
        foreach (range(1, 11) as $i) {
            $this->activity("old {$i}", '2026-08-01 00:00:00');
        }
        $this->activity('keep', '2026-10-19 00:00:00');

        $queries = 0;
        DB::listen(function ($query) use (&$queries): void {
            if (str_starts_with(strtolower($query->sql), 'delete from "activity_log"')) {
                $queries++;
            }
        });

        $this->prune([Activity::class], chunk: 3);

        $this->assertSame(['keep'], $this->remainingActivity());
        $this->assertSame(5, $queries, '11 rows in chunks of 3 = 4 deleting statements plus 1 that finds nothing left');
    }

    public function test_the_retention_period_can_be_changed_in_config(): void
    {
        config(['log_retention.days' => 10]);

        $this->activity('12 days old', '2026-10-08 12:00:00');
        $this->activity('10 days old', '2026-10-10 12:00:00');
        $this->activity('9 days old', '2026-10-11 12:00:00');

        $this->prune();

        $this->assertSame(['10 days old', '9 days old'], $this->remainingActivity());
    }

    public function test_pretend_mode_reports_without_deleting(): void
    {
        $this->activity('old', '2026-08-01 00:00:00');

        $this->artisan('model:prune', ['--model' => [Activity::class], '--pretend' => true])
            ->expectsOutputToContain('1 [App\Models\Activity] records will be pruned.')
            ->assertSuccessful();

        $this->assertSame(['old'], $this->remainingActivity());
    }

    public function test_an_empty_run_does_nothing_and_succeeds(): void
    {
        $this->prune();

        $this->assertSame(0, DB::table('activity_log')->count());
    }

    public function test_the_models_use_mass_pruning_so_rows_are_not_loaded_into_memory(): void
    {
        foreach (self::MODELS as $model) {
            $this->assertContains(MassPrunable::class, class_uses_recursive($model), $model);
        }
    }

    public function test_the_activity_log_package_uses_the_pruning_model(): void
    {
        $this->assertInstanceOf(Activity::class, ActivitylogServiceProvider::getActivityModelInstance());
        $this->assertSame(30, config('log_retention.days'));
        $this->assertSame(30, config('activitylog.delete_records_older_than_days'));
    }

    public function test_the_cleanup_is_scheduled_once_a_day_for_exactly_these_three_models(): void
    {
        $event = collect(app(Schedule::class)->events())->first(fn ($event): bool => $event->description === 'log-retention');

        $this->assertNotNull($event, 'the log retention cleanup is not scheduled');
        $this->assertSame('0 3 * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
        $this->assertStringContainsString('model:prune', $event->command);

        foreach (self::MODELS as $model) {
            $this->assertStringContainsString($model, str_replace('\\\\', '\\', $event->command));
        }

        $this->assertSame(3, substr_count($event->command, '--model='), 'only the three log models are named');
    }

    public function test_the_retention_indexes_exist_and_can_be_removed(): void
    {
        $this->assertTrue(Schema::hasIndex('activity_log', ['created_at']));
        $this->assertTrue(Schema::hasIndex('maintenance_inspection_logs', ['logged_at']));
        $this->assertTrue(Schema::hasIndex('aims_inspection_logs', ['logged_at']));

        (require base_path('database/migrations/2026_10_20_105530_add_retention_indexes_to_log_tables.php'))->down();

        $this->assertFalse(Schema::hasIndex('activity_log', ['created_at']));
        $this->assertFalse(Schema::hasIndex('maintenance_inspection_logs', ['logged_at']));
        $this->assertFalse(Schema::hasIndex('aims_inspection_logs', ['logged_at']));
    }
}

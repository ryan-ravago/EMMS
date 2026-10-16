<?php

namespace Tests\Feature;

use App\Models\LifecycleLog;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LifecycleLogNameSnapshotTest extends TestCase
{
    private const string SNAPSHOT_MIGRATION = 'database/migrations/2026_10_16_104717_snapshot_names_on_lifecycle_logs_table.php';

    private const string SET_NULL_MIGRATION = 'database/migrations/2026_10_16_114441_set_null_on_delete_for_deploy_to_loc_id_on_lifecycle_logs_table.php';

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_mname')->nullable();
            $table->string('user_lname')->nullable();
        });

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->string('eqm_name');
        });

        Schema::create('actions', function (Blueprint $table): void {
            $table->string('a_id', 10)->primary();
        });

        Schema::create('statuses', function (Blueprint $table): void {
            $table->string('status_id', 10)->primary();
        });

        (require base_path('database/migrations/2026_09_10_082824_create_lifecycle_logs_table.php'))->up();
        (require base_path('database/migrations/2026_09_10_141030_add_foreign_keys_to_lifecycle_logs_table.php'))->up();
        (require base_path('database/migrations/2026_09_15_083013_add_operator_to_lifecycle_logs_table.php'))->up();

        DB::table('app_users')->insert(['user_id' => 1, 'user_fname' => 'Juan', 'user_mname' => 'Santos', 'user_lname' => 'Dela Cruz']);
        DB::table('locations')->insert(['id' => 3, 'name' => 'Main Yard']);
        DB::table('equipment_units')->insert([
            ['eqm_id' => 10, 'eqm_name' => 'GPS Tracker'],
            ['eqm_id' => 20, 'eqm_name' => 'Dump Truck'],
        ]);
        DB::table('actions')->insert([['a_id' => 'dep'], ['a_id' => 'alc']]);
        DB::table('statuses')->insert([['status_id' => 'dep'], ['status_id' => 'alc']]);
    }

    public function test_migration_backfills_name_snapshots_for_existing_logs(): void
    {
        DB::table('lifecycle_logs')->insert([
            'asset_id' => 10,
            'action_id' => 'dep',
            'status_id' => 'dep',
            'deploy_to_loc_id' => 3,
            'allocate_to_equipment_id' => 20,
            'performed_by' => 1,
            'logged_at' => now(),
        ]);

        $this->runSnapshotMigration();

        $this->assertDatabaseHas('lifecycle_logs', [
            'deploy_to_loc_name' => 'Main Yard',
            'allocate_to_equipment_name' => 'Dump Truck',
            'performed_by_name' => 'Juan Santos Dela Cruz',
        ]);
    }

    public function test_location_with_logs_can_be_deleted_and_the_log_keeps_its_name(): void
    {
        $this->runSnapshotMigration();
        $this->createLog(['deploy_to_loc_id' => 3]);

        DB::table('locations')->where('id', 3)->delete();

        $log = LifecycleLog::query()->sole();
        $this->assertNull($log->deploy_to_loc_id);
        $this->assertSame('Main Yard', $log->deploy_to_loc_name);
    }

    public function test_migration_clears_ids_of_locations_that_were_already_deleted(): void
    {
        (require base_path(self::SNAPSHOT_MIGRATION))->up();
        $this->createLog(['deploy_to_loc_id' => 3]);
        DB::table('locations')->where('id', 3)->delete();

        (require base_path(self::SET_NULL_MIGRATION))->up();

        $log = LifecycleLog::query()->sole();
        $this->assertNull($log->deploy_to_loc_id);
        $this->assertSame('Main Yard', $log->deploy_to_loc_name);
    }

    public function test_allocated_equipment_and_user_can_be_deleted_and_the_log_keeps_their_names(): void
    {
        $this->runSnapshotMigration();
        $this->createLog(['allocate_to_equipment_id' => 20, 'performed_by' => 1]);

        DB::table('equipment_units')->where('eqm_id', 20)->delete();
        DB::table('app_users')->where('user_id', 1)->delete();

        $log = LifecycleLog::query()->sole();
        $this->assertSame('Dump Truck', $log->allocate_to_equipment_name);
        $this->assertSame('Juan Santos Dela Cruz', $log->performed_by_name);
    }

    public function test_renaming_a_location_does_not_change_old_logs(): void
    {
        $this->runSnapshotMigration();
        $this->createLog(['deploy_to_loc_id' => 3]);

        DB::table('locations')->where('id', 3)->update(['name' => 'North Yard']);

        $this->assertSame('Main Yard', LifecycleLog::query()->sole()->deploy_to_loc_name);
    }

    public function test_asset_with_logs_still_cannot_be_deleted(): void
    {
        $this->runSnapshotMigration();
        $this->createLog();

        $this->expectException(QueryException::class);

        DB::table('equipment_units')->where('eqm_id', 10)->delete();
    }

    public function test_explicit_snapshot_names_are_not_overwritten(): void
    {
        $this->runSnapshotMigration();
        $this->createLog(['deploy_to_loc_id' => 3, 'deploy_to_loc_name' => 'Given Name']);

        $this->assertSame('Given Name', LifecycleLog::query()->sole()->deploy_to_loc_name);
    }

    public function test_logs_without_references_have_no_names(): void
    {
        $this->runSnapshotMigration();
        $this->createLog();

        $log = LifecycleLog::query()->sole();
        $this->assertNull($log->deploy_to_loc_name);
        $this->assertNull($log->allocate_to_equipment_name);
        $this->assertNull($log->performed_by_name);
    }

    public function test_snapshot_helper_fills_the_performer_for_bulk_inserts(): void
    {
        $this->assertSame(
            ['performed_by' => 1, 'performed_by_name' => 'Juan Santos Dela Cruz'],
            LifecycleLog::withNameSnapshots(['performed_by' => 1]),
        );
        $this->assertSame(
            ['performed_by' => 999, 'performed_by_name' => null],
            LifecycleLog::withNameSnapshots(['performed_by' => 999]),
        );
    }

    public function test_lifecycle_logs_cannot_be_deleted(): void
    {
        $this->runSnapshotMigration();
        $log = $this->createLog();

        try {
            $log->delete();
            $this->fail('Deleting a lifecycle log should throw.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('permanent', $exception->getMessage());
        }

        $this->assertSame(1, LifecycleLog::query()->count());
    }

    public function test_migration_can_be_rolled_back(): void
    {
        $this->runSnapshotMigration();

        (require base_path(self::SET_NULL_MIGRATION))->down();
        (require base_path(self::SNAPSHOT_MIGRATION))->down();

        $this->assertFalse(Schema::hasColumn('lifecycle_logs', 'deploy_to_loc_name'));
        $this->assertFalse(Schema::hasColumn('lifecycle_logs', 'performed_by_name'));
    }

    private function runSnapshotMigration(): void
    {
        (require base_path(self::SNAPSHOT_MIGRATION))->up();
        (require base_path(self::SET_NULL_MIGRATION))->up();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createLog(array $attributes = []): LifecycleLog
    {
        return LifecycleLog::query()->create([
            'asset_id' => 10,
            'action_id' => 'dep',
            'status_id' => 'dep',
            'logged_at' => now(),
            ...$attributes,
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes for the queries the dashboards run on tables that keep growing (scans, work orders, trips).
     * table => [index name => columns]
     *
     * @var array<string, array<string, list<string>>>
     */
    private array $indexes = [
        // "Scans today" and "last scan": the existing unique index starts with yard_id, so these scanned all of it.
        'asset_tag_logs' => ['asset_tag_logs_detected_at_index' => ['detected_at']],
        // "Equipment without an active tag" looks up a unit's tags by their retired flag.
        'asset_tags' => ['asset_tags_asset_parent_id_is_deleted_index' => ['asset_parent_id', 'is_deleted']],
        // Open work orders per status and department, and a requestor's latest requests.
        'work_orders' => [
            'work_orders_wo_status_id_wo_dep_id_index' => ['wo_status_id', 'wo_dep_id'],
            'work_orders_wo_created_by_wo_created_dt_index' => ['wo_created_by', 'wo_created_dt'],
        ],
        // "Equipment currently out": trips with no time in, newest time out first.
        'asset_movements' => ['asset_movements_time_in_time_out_index' => ['time_in', 'time_out']],
        // A department's upcoming and overdue tasks.
        'maintenance_tasks' => ['maintenance_tasks_mt_dep_id_mt_status_id_mt_due_dt_index' => ['mt_dep_id', 'mt_status_id', 'mt_due_dt']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (! Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
                }
            }
        }
    }
};

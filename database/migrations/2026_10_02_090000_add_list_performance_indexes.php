<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $indexes = [
        'work_orders' => ['wo_created_dt'],
        'inspections' => ['ins_submitted_dt'],
        'equipment_units' => ['eqm_is_active'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $columns) {
            foreach ($columns as $column) {
                $name = "{$table}_{$column}_index";

                if (! Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn(Blueprint $t) => $t->index($column, $name));
                }
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $columns) {
            foreach ($columns as $column) {
                $name = "{$table}_{$column}_index";

                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn(Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }
};

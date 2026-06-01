<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment_tasks_schedules', function (Blueprint $table) {
            $table->unique(
                ['ets_dep_id', 'ets_eqm_id', 'ets_task_id'],
                'ets_dep_eqm_task_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_tasks_schedules', function (Blueprint $table) {
            $table->dropUnique('ets_dep_eqm_task_unique');
        });
    }
};

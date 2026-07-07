<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `equipment_type_task_checklist_template` DROP INDEX `etct_dep_eqm_task_unique`');

        Schema::table('equipment_type_task_checklist_template', function (Blueprint $table) {
            $table->dropColumn('etct_eqm_id');
        });

        Schema::table('equipment_type_task_checklist_template', function (Blueprint $table) {
            $table->foreign('etct_dep_id')
                ->references('dep_id')
                ->on('departments');

            $table->foreign('etct_eqmt_id')
                ->references('eqmt_id')
                ->on('equipment_types');

            $table->foreign('etct_task_id')
                ->references('task_id')
                ->on('tasks');

            $table->unique(['etct_dep_id', 'etct_eqmt_id', 'etct_task_id'], 'etct_dep_eqmt_task_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_type_task_checklist_template', function (Blueprint $table) {
            $table->dropForeign(['etct_dep_id']);
            $table->dropForeign(['etct_eqmt_id']);
            $table->dropForeign(['etct_task_id']);
            $table->dropUnique(['etct_dep_id', 'etct_eqmt_id', 'etct_task_id']);
        });

        Schema::table('equipment_type_task_checklist_template', function (Blueprint $table) {
            $table->unsignedBigInteger('etct_eqm_id')->nullable();
            $table->unique(['etct_dep_id', 'etct_eqm_id', 'etct_task_id'], 'etct_dep_eqm_task_unique');
        });
    }
};

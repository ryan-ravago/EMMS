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
        Schema::table('equipment_task_checklist_template', function (Blueprint $table) {
            $table->foreignId('etct_eqmt_id')
                ->nullable()
                ->after('etct_eqm_id');

            $table->foreign('etct_eqmt_id')
                ->references('eqmt_id')
                ->on('equipment_types')
                ->nullOnDelete();

            $table->index(['etct_eqmt_id', 'etct_dep_id', 'etct_task_id'], 'etct_eqmt_dep_task_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_task_checklist_template', function (Blueprint $table) {
            $table->dropIndex('etct_eqmt_dep_task_index');
            $table->dropForeign(['etct_eqmt_id']);
            $table->dropColumn('etct_eqmt_id');
        });
    }
};

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
            $table->unique(['etct_dep_id', 'etct_eqm_id', 'etct_task_id'], 'etct_dep_eqm_task_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_task_checklist_template', function (Blueprint $table) {
            $table->dropUnique('etct_dep_eqm_task_unique');
        });
    }
};

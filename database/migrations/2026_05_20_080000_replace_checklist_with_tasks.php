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
        // 1. Drop foreign key constraints from tables referencing checklists
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropForeign(['mt_clt_id']);
            $table->dropForeign(['mt_cli_id']);
        });

        // 2. Drop the tables we are removing
        Schema::dropIfExists('equipment_checklist_assignments');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklist_templates');
        Schema::dropIfExists('checklist_usage_types');

        // 3. Create task_usage_types table
        Schema::create('task_usage_types', function (Blueprint $table) {
            $table->integer('tut_id')->autoIncrement();
            $table->string('tut_name');
        });

        // 4. Create tasks table
        Schema::create('tasks', function (Blueprint $table) {
            $table->id('task_id');
            $table->string('task_name');
            
            $table->unsignedBigInteger('task_dep_id');
            $table->foreign('task_dep_id')
                  ->references('dep_id')
                  ->on('departments')
                  ->onDelete('restrict');

            $table->integer('task_tut_id');
            $table->foreign('task_tut_id')
                  ->references('tut_id')
                  ->on('task_usage_types')
                  ->onDelete('restrict');

            $table->unsignedBigInteger('task_created_by')->nullable();
            $table->foreign('task_created_by')
                  ->references('user_id')
                  ->on('app_users')
                  ->onDelete('restrict');
                  
            $table->dateTime('task_created_at');

            $table->unsignedBigInteger('task_last_updated_by')->nullable();
            $table->foreign('task_last_updated_by')
                  ->references('dep_id')
                  ->on('departments')
                  ->onDelete('restrict');
                  
            $table->dateTime('task_last_updated_at');
        });

        // 5. Create equipment_task_checklist_template table
        Schema::create('equipment_task_checklist_template', function (Blueprint $table) {
            $table->id('etct_id');

            $table->unsignedBigInteger('etct_dep_id');
            $table->foreign('etct_dep_id')
                  ->references('dep_id')
                  ->on('departments')
                  ->onDelete('restrict');

            $table->unsignedBigInteger('etct_eqm_id');
            $table->foreign('etct_eqm_id')
                  ->references('eqm_id')
                  ->on('equipment_units')
                  ->onDelete('restrict');

            $table->unsignedBigInteger('etct_task_id');
            $table->foreign('etct_task_id')
                  ->references('task_id')
                  ->on('tasks')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('etct_created_by')->nullable();
            $table->foreign('etct_created_by')
                  ->references('user_id')
                  ->on('app_users')
                  ->onDelete('restrict');

            $table->dateTime('etct_created_at');
        });

        // 6. Create equipment_tasks_schedules table
        Schema::create('equipment_tasks_schedules', function (Blueprint $table) {
            $table->id('ets_id');

            $table->unsignedBigInteger('ets_dep_id');
            $table->foreign('ets_dep_id')
                  ->references('dep_id')
                  ->on('departments')
                  ->onDelete('restrict');

            $table->unsignedBigInteger('ets_eqm_id');
            $table->foreign('ets_eqm_id')
                  ->references('eqm_id')
                  ->on('equipment_units')
                  ->onDelete('restrict');

            $table->unsignedBigInteger('ets_task_id');
            $table->foreign('ets_task_id')
                  ->references('task_id')
                  ->on('tasks')
                  ->onDelete('cascade');

            $table->integer('ets_sort_order')->default(0);
            $table->integer('ets_itrv_years')->nullable();
            $table->integer('ets_itrv_months')->nullable();
            $table->integer('ets_itrv_weeks')->nullable();
            $table->integer('ets_itrv_days')->nullable();
            $table->time('ets_sched_time')->nullable();
            $table->dateTime('ets_due_effectivity_dt');
            $table->dateTime('ets_due_dt')->nullable();

            $table->unsignedBigInteger('ets_assigned_by')->nullable();
            $table->foreign('ets_assigned_by')
                  ->references('user_id')
                  ->on('app_users')
                  ->onDelete('restrict');

            $table->dateTime('ets_assigned_at');
        });

        // 7. Update maintenance_tasks table
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['mt_clt_id', 'mt_clt_log', 'mt_cli_id', 'mt_cli_log']);

            // Add new columns
            $table->unsignedBigInteger('mt_ets_id')->nullable();
            $table->foreign('mt_ets_id')
                  ->references('ets_id')
                  ->on('equipment_tasks_schedules')
                  ->onDelete('set null');

            $table->unsignedBigInteger('mt_task_id');
            $table->foreign('mt_task_id')
                  ->references('task_id')
                  ->on('tasks')
                  ->onDelete('restrict');

            $table->string('mt_task_log')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropForeign(['mt_ets_id']);
            $table->dropForeign(['mt_task_id']);
            $table->dropColumn(['mt_ets_id', 'mt_task_id', 'mt_task_log']);

            $table->unsignedBigInteger('mt_clt_id')->nullable();
            $table->string('mt_clt_log')->nullable();
            $table->unsignedBigInteger('mt_cli_id')->nullable();
            $table->string('mt_cli_log')->nullable();
        });

        Schema::dropIfExists('equipment_tasks_schedules');
        Schema::dropIfExists('equipment_task_checklist_template');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_usage_types');

        // Recreate checklist_usage_types
        Schema::create('checklist_usage_types', function (Blueprint $table) {
            $table->integer('cut_id')->autoIncrement();
            $table->string('cut_name', 60);
        });

        // Recreate checklist_templates
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id('clt_id');
            $table->string('clt_name');
            $table->unsignedInteger('clt_cut_id');
            $table->foreignId('clt_dep_id')->constrained('departments', 'dep_id');
            $table->integer('clt_interval_years')->nullable();
            $table->integer('clt_interval_months')->nullable();
            $table->integer('clt_interval_weeks')->nullable();
            $table->integer('clt_interval_days')->nullable();
            $table->time('clt_schedule_time')->nullable();
            $table->foreignId('clt_created_by')->constrained('app_users', 'user_id');
            $table->dateTime('clt_created_dt');
            $table->foreignId('clt_last_updated_by')->constrained('app_users', 'user_id');
            $table->dateTime('clt_last_updated_dt');
        });

        // Recreate checklist_items
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id('cli_id');
            $table->unsignedBigInteger('cli_clt_id');
            $table->string('cli_name');
            $table->integer('cli_sort_order');
            $table->unsignedInteger('cli_created_by');
            $table->dateTime('cli_created_at');
            $table->unsignedInteger('cli_last_updated_by');
            $table->dateTime('cli_last_updated_at');
        });

        // Recreate equipment_checklist_assignments
        Schema::create('equipment_checklist_assignments', function (Blueprint $table) {
            $table->id('eca_id');
            $table->unsignedBigInteger('eca_clt_id');
            $table->unsignedBigInteger('eca_eqm_id');
            $table->dateTime('eca_due_effectivity_dt')->nullable();
            $table->dateTime('eca_due_dt')->nullable();
            $table->unsignedInteger('eca_assigned_by')->nullable();
            $table->dateTime('eca_assigned_at');
        });

        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->foreign('mt_clt_id')->references('clt_id')->on('checklist_templates');
            $table->foreign('mt_cli_id')->references('cli_id')->on('checklist_items');
        });
    }
};

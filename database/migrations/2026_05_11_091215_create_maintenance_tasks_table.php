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
        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->bigIncrements('mt_id');

            $table->unsignedBigInteger('mt_eqm_id');
            $table->string('mt_eqm_log')->nullable();
            $table->foreign('mt_eqm_id')->references('eqm_id')->on('equipment_units');

            $table->unsignedBigInteger('mt_dep_id');
            $table->foreign('mt_dep_id')->references('dep_id')->on('departments');

            $table->unsignedBigInteger('mt_clt_id');
            $table->string('mt_clt_log')->nullable();
            $table->foreign('mt_clt_id')->references('clt_id')->on('checklist_templates');

            $table->unsignedBigInteger('mt_cli_id');
            $table->string('mt_cli_log')->nullable();
            $table->foreign('mt_cli_id')->references('cli_id')->on('checklist_items');

            $table->string('mt_status_id', 10);
            $table->foreign('mt_status_id')->references('status_id')->on('statuses');

            $table->datetime('mt_due_dt')->nullable();

            $table->text('mt_remarks')->nullable();
            $table->datetime('mt_scheduled_dt');
            $table->datetime('mt_closed_dt')->nullable();

            $table->unsignedBigInteger('mt_by');
            $table->foreign('mt_by')->references('user_id')->on('app_users');

            $table->datetime('mt_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropForeign(['mt_eqm_id']);
            $table->dropForeign(['mt_dep_id']);
            $table->dropForeign(['mt_clt_id']);
            $table->dropForeign(['mt_cli_id']);
            $table->dropForeign(['mt_status_id']);
        });

        Schema::dropIfExists('maintenance_tasks');
    }
};

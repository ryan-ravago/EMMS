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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id('wo_id');
            $table->string('wo_no');
            $table->foreignId('wo_eqm_id')->constrained('equipment_units', 'eqm_id');
            $table->foreignId('wo_dep_id')->constrained('departments', 'dep_id');
            $table->foreignId('wo_mt_id')->nullable()->constrained('maintenance_tasks', 'mt_id');
            $table->unsignedBigInteger('wo_insi_id')->nullable();
            $table->text('wo_title');
            $table->text('wo_desc');
            $table->unsignedInteger('wo_prio_id');
            $table->foreign('wo_prio_id')->references('prio_id')->on('priorities');
            $table->string('wo_status_id');
            $table->foreign('wo_status_id')->references('status_id')->on('statuses');
            $table->text('wo_attachments')->nullable();
            $table->foreignId('wo_created_by')->constrained('app_users', 'user_id');
            $table->dateTime('wo_created_dt');
            $table->dateTime('wo_closed_dt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};

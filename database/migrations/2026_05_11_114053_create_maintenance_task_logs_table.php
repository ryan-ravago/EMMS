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
        Schema::create('maintenance_task_logs', function (Blueprint $table) {
            $table->bigIncrements('mtl_id');

            $table->unsignedBigInteger('mtl_mt_id');
            $table->foreign('mtl_mt_id')->references('mt_id')->on('maintenance_tasks');

            $table->string('mtl_status_id', 10);
            $table->foreign('mtl_status_id')->references('status_id')->on('statuses');

            $table->datetime('mtl_due_dt')->nullable();

            $table->string('mtl_last_act_made', 10)->nullable();
            $table->foreign('mtl_last_act_made')->references('a_id')->on('actions');

            $table->text('mtl_remarks')->nullable();

            $table->unsignedBigInteger('mtl_by')->nullable();
            $table->foreign('mtl_by')->references('user_id')->on('app_users');

            $table->datetime('mtl_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_task_logs', function (Blueprint $table) {
            $table->dropForeign(['mtl_mt_id']);
            $table->dropForeign(['mtl_status_id']);
            $table->dropForeign(['mtl_last_act_made']);
            $table->dropForeign(['mtl_by']);
        });

        Schema::dropIfExists('maintenance_task_logs');
    }
};

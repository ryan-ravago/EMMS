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
        Schema::table('work_order_assignments', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['woa_wo_id']);
            $table->dropForeign(['woa_worker_id']);

            // Remove auto_increment
            $table->unsignedBigInteger('woa_id')->change();
        });

        Schema::table('work_order_assignments', function (Blueprint $table) {
            // Drop primary key and unnecessary columns
            $table->dropPrimary();
            $table->dropColumn(['woa_id', 'woa_assigned_dt']);

            // Set composite primary key
            $table->primary(['woa_wo_id', 'woa_worker_id']);

            // Re-add foreign keys
            $table->foreign('woa_wo_id')->references('wo_id')->on('work_orders')->cascadeOnDelete();
            $table->foreign('woa_worker_id')->references('user_id')->on('app_users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_order_assignments', function (Blueprint $table) {
            $table->dropForeign(['woa_wo_id']);
            $table->dropForeign(['woa_worker_id']);
            $table->dropPrimary(['woa_wo_id', 'woa_worker_id']);

            $table->id('woa_id');
            $table->dateTime('woa_assigned_dt');

            $table->foreign('woa_wo_id')->references('wo_id')->on('work_orders')->cascadeOnDelete();
            $table->foreign('woa_worker_id')->references('user_id')->on('app_users')->cascadeOnDelete();
        });
    }
};

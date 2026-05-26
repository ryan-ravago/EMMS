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
        Schema::create('work_order_logs', function (Blueprint $table) {
            $table->id('wol_id');
            $table->foreignId('wol_wo_id')->constrained('work_orders', 'wo_id');
            $table->string('wol_a_id', 10);
            $table->foreign('wol_a_id')->references('a_id')->on('actions');
            $table->string('wol_status_id', 10);
            $table->foreign('wol_status_id')->references('status_id')->on('statuses');
            $table->string('wol_a_log');
            $table->string('wol_status_log');
            $table->foreignId('wol_by')->constrained('app_users', 'user_id');
            $table->dateTime('wol_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_logs');
    }
};

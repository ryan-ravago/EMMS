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
        Schema::create('work_order_assignments', function (Blueprint $table) {
            $table->id('woa_id');
            $table->foreignId('woa_wo_id')->constrained('work_orders', 'wo_id');
            $table->foreignId('woa_worker_id')->constrained('app_users', 'user_id');
            $table->dateTime('woa_assigned_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_assignments');
    }
};

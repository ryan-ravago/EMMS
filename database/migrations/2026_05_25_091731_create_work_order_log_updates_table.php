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
        Schema::create('work_order_log_updates', function (Blueprint $table) {
            $table->id('wolu_id');
            $table->foreignId('wolu_wo_id')->constrained('work_orders', 'wo_id');
            $table->text('wolu_update_note');
            $table->text('wolu_attachments')->nullable();
            $table->foreignId('wolu_by')->constrained('app_users', 'user_id');
            $table->dateTime('wolu_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_log_updates');
    }
};

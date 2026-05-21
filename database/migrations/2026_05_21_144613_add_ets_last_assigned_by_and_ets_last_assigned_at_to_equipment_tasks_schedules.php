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
        Schema::table('equipment_tasks_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('ets_last_assigned_by')->nullable()->after('ets_assigned_at');
            $table->dateTime('ets_last_assigned_at')->nullable()->after('ets_last_assigned_by');

            $table->foreign('ets_last_assigned_by')
                ->references('user_id')
                ->on('app_users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_tasks_schedules', function (Blueprint $table) {
            $table->dropForeign(['ets_last_assigned_by']);
            $table->dropColumn(['ets_last_assigned_by', 'ets_last_assigned_at']);
        });
    }
};

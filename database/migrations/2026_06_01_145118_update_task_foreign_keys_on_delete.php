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
        // maintenance_tasks: mt_task_id → SET NULL on delete
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropForeign(['mt_task_id']);
            $table->foreignId('mt_task_id')
                ->nullable()
                ->change();
            $table->foreign('mt_task_id')
                ->references('task_id')
                ->on('tasks')
                ->nullOnDelete();
        });

        // equipment_tasks_schedules: ets_task_id → CASCADE on delete
        Schema::table('equipment_tasks_schedules', function (Blueprint $table) {
            $table->dropForeign(['ets_task_id']);
            $table->foreign('ets_task_id')
                ->references('task_id')
                ->on('tasks')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropForeign(['mt_task_id']);
            $table->foreignId('mt_task_id')
                ->nullable(false)
                ->change();
            $table->foreign('mt_task_id')
                ->references('task_id')
                ->on('tasks')
                ->restrictOnDelete();
        });

        Schema::table('equipment_tasks_schedules', function (Blueprint $table) {
            $table->dropForeign(['ets_task_id']);
            $table->foreign('ets_task_id')
                ->references('task_id')
                ->on('tasks')
                ->restrictOnDelete();
        });
    }
};

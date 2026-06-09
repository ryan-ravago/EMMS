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
        Schema::table('maintenance_task_logs', function (Blueprint $table) {
            $table->foreignId('mtl_wo_id')
                ->after('mtl_last_act_made')
                ->nullable()
                ->constrained('work_orders', 'wo_id')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_task_logs', function (Blueprint $table) {
            $table->dropForeign(['mtl_wo_id']);
            $table->dropColumn('mtl_wo_id');
        });
    }
};

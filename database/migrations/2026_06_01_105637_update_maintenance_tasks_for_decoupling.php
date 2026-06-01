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
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            // 1. Drop the old foreign key constraint safely
            // Note: If your foreign key follows standard Laravel naming conventions, use this:
            $table->dropForeign(['mt_ets_id']);

            // 2. Drop the column entirely
            $table->dropColumn('mt_ets_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            // 1. Restore the column (adjust the data type if it was a different integer type)
            $table->unsignedBigInteger('mt_ets_id')->nullable()->after('mt_dt');

            // 2. Restore the foreign key relationship constraint back to the schedules table
            $table->foreign('mt_ets_id')
                ->references('ets_id')
                ->on('equipment_tasks_schedules')
                ->onDelete('set null'); // Or cascade depending on your old design
        });
    }
};

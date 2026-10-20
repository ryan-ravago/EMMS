<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The daily cleanup finds old rows by their date. Without an index on that date every run would scan the whole
     * table, so each log table gets one: activity_log.created_at and the inspection logs' logged_at.
     */
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('maintenance_inspection_logs', function (Blueprint $table) {
            $table->index('logged_at');
        });

        Schema::table('aims_inspection_logs', function (Blueprint $table) {
            $table->index('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aims_inspection_logs', function (Blueprint $table) {
            $table->dropIndex(['logged_at']);
        });

        Schema::table('maintenance_inspection_logs', function (Blueprint $table) {
            $table->dropIndex(['logged_at']);
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};

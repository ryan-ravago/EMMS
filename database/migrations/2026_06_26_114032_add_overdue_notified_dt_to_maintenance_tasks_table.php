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
            $table->date('mt_overdue_notified_dt')->nullable()->after('mt_closed_dt');
            $table->index('mt_overdue_notified_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropIndex(['mt_overdue_notified_dt']);
            $table->dropColumn('mt_overdue_notified_dt');
        });
    }
};

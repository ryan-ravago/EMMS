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
        Schema::table('work_order_logs', function (Blueprint $table) {
            $table->text('wol_note')->nullable()->after('wol_a_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_logs', function (Blueprint $table) {
            $table->dropColumn('wol_note');
        });
    }
};

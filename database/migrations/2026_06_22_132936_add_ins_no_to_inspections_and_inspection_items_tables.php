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
        Schema::table('inspections', function (Blueprint $table) {
            $table->string('ins_no')->nullable()->unique()->after('ins_id');
        });

        Schema::table('inspection_items', function (Blueprint $table) {
            $table->string('insi_no')->nullable()->unique()->after('insi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn('ins_no');
        });

        Schema::table('inspection_items', function (Blueprint $table) {
            $table->dropColumn('insi_no');
        });
    }
};

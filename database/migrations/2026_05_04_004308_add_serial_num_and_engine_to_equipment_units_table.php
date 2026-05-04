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
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->string('eqm_serial_num')->nullable()->unique()->after('eqm_prc_code');
            $table->string('eqm_engine', 255)->nullable()->after('eqm_serial_num');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropUnique(['eqm_serial_num']);
            $table->dropColumn(['eqm_serial_num', 'eqm_engine']);
        });
    }
};

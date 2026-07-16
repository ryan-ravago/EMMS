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
            $table->string('eqm_chassis_no')
                ->nullable()
                ->after('eqm_eqmt_id');

            $table->date('eqm_date_purchased')
                ->nullable()
                ->after('eqm_chassis_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropColumn([
                'eqm_chassis_no',
                'eqm_date_purchased',
            ]);
        });
    }
};

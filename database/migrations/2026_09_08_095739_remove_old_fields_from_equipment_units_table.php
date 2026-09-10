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
            // Drop foreign key first
            $table->dropForeign(['eqm_eqmt_id']);

            // Remove old fields
            $table->dropColumn([
                'eqm_eqmt_id',
                'eqm_engine',
                'eqm_vin',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            // Restore old fields
            $table->foreignId('eqm_eqmt_id')
                ->nullable()
                ->constrained('equipment_types', 'eqmt_id');

            $table->string('eqm_engine')->nullable();
            $table->string('eqm_vin')->nullable();
        });
    }
};

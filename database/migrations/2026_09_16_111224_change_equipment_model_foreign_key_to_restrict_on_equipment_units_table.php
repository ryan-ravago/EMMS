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
            $table->dropForeign(['eqm_eqmm_id']);
        });

        Schema::table('equipment_units', function (Blueprint $table) {
            $table->foreign('eqm_eqmm_id')
                ->references('eqmm_id')
                ->on('equipment_models')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropForeign(['eqm_eqmm_id']);
        });

        Schema::table('equipment_units', function (Blueprint $table) {
            $table->foreign('eqm_eqmm_id')
                ->references('eqmm_id')
                ->on('equipment_models')
                ->cascadeOnDelete();
        });
    }
};

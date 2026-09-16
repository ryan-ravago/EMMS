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
        Schema::table('equipment_models', function (Blueprint $table) {
            $table->dropForeign('equipment_models_eqmm_fuel_type_foreign');

            $table->dropColumn([
                'eqmm_fuel_type',
                'eqmm_max_capacity_tons',
                'eqmm_max_reach_meters',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_models', function (Blueprint $table) {
            $table->unsignedBigInteger('eqmm_fuel_type')->nullable();
            $table->decimal('eqmm_max_capacity_tons', 8, 2)->nullable();
            $table->decimal('eqmm_max_reach_meters', 8, 2)->nullable();
        });

        Schema::table('equipment_models', function (Blueprint $table) {
            // Replace 'fuel_types' and 'id' with the actual referenced table/column.
            $table->foreign('eqmm_fuel_type')
                ->references('id')
                ->on('fuel_types');
        });
    }
};

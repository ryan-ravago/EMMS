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
            $table->foreignId('eqmm_eqmt_id')
                ->nullable()
                ->constrained('equipment_types', 'eqmt_id')
                ->nullOnDelete();
            $table->decimal('eqmm_max_capacity_tons', 8, 2)
                ->nullable()
                ->after('eqmm_eqmt_id');
            $table->decimal('eqmm_max_reach_meters', 8, 2)
                ->nullable()
                ->after('eqmm_max_capacity_tons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_models', function (Blueprint $table) {
            $table->dropForeign(['eqmm_eqmt_id']);
            $table->dropColumn([
                'eqmm_eqmt_id',
                'eqmm_max_capacity_tons',
                'eqmm_max_reach_meters',
            ]);
        });
    }
};

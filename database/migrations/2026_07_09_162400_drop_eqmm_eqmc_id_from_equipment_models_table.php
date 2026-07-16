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
            $table->dropConstrainedForeignId('eqmm_eqmc_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_models', function (Blueprint $table) {
            $table->foreignId('eqmm_eqmc_id')
                ->nullable()
                ->constrained('equipment_categories', 'eqmc_id')
                ->nullOnDelete();
        });
    }
};

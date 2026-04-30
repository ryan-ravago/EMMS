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
        Schema::create('equipment_models', function (Blueprint $table) {
            // Primary Key (using BigInt as per your original design)
            $table->id('eqmm_id');

            // The Model Name
            $table->string('eqmm_name', 150);

            // Foreign Keys
            // constrained() automatically looks for the plural table name
            $table->foreignId('eqmm_eqmc_id')
                ->nullable()
                ->constrained('equipment_categories', 'eqmc_id')
                ->onDelete('set null'); // If a category is deleted, keep the model but set category to null

            $table->foreignId('eqmm_brand_id')
                ->nullable()
                ->constrained('equipment_brands', 'eqmb_id')
                ->onDelete('set null');

            $table->foreignId('eqmm_fuel_type')
                ->nullable()
                ->constrained('fuel_types', 'fuel_id')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_models');
    }
};

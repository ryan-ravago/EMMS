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
        Schema::create('equipment_units', function (Blueprint $table) {
            $table->id('eqm_id');

            // Foreign key
            $table->foreignId('eqm_eqmm_id')
                ->constrained('equipment_models', 'eqmm_id')
                ->cascadeOnDelete();

            $table->string('eqm_name');
            $table->string('eqm_vin')->nullable();
            $table->string('eqm_plate_num')->nullable();

            $table->string('eqm_prc_code', 20)->nullable();

            $table->boolean('eqm_is_active')->default(true);

            $table->dateTime('eqm_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};

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
            $table->foreignId('eqm_brand_id')
                ->nullable()
                ->after('eqm_eqmm_id')
                ->constrained('equipment_brands', 'eqmb_id')
                ->nullOnDelete();

            $table->foreignId('eqm_eqmt_id')
                ->nullable()
                ->after('eqm_brand_id')
                ->constrained('equipment_types', 'eqmt_id')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropForeign(['eqm_brand_id']);
            $table->dropForeign(['eqm_eqmt_id']);

            $table->dropColumn([
                'eqm_brand_id',
                'eqm_eqmt_id',
            ]);
        });
    }
};

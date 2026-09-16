<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->foreignId('eqm_eqmt_id')
                ->nullable()
                ->after('eqm_brand_id');
        });

        DB::statement(<<<'SQL'
            UPDATE equipment_units AS equipment
            INNER JOIN equipment_models AS models
                ON models.eqmm_id = equipment.eqm_eqmm_id
            SET equipment.eqm_eqmt_id = models.eqmm_eqmt_id
            WHERE equipment.eqm_eqmt_id IS NULL
        SQL);

        Schema::table('equipment_units', function (Blueprint $table) {
            $table->foreign('eqm_eqmt_id')
                ->references('eqmt_id')
                ->on('equipment_types')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropForeign(['eqm_eqmt_id']);
            $table->dropColumn('eqm_eqmt_id');
        });
    }
};

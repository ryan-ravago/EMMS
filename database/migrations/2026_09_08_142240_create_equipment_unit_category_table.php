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
        // 1. Create the pivot table
        Schema::create('equipment_unit_category', function (Blueprint $table) {
            $table->unsignedBigInteger('eqm_id');
            $table->unsignedBigInteger('eqmc_id');

            $table->foreign('eqm_id')
                ->references('eqm_id')
                ->on('equipment_units')
                ->cascadeOnDelete();

            $table->foreign('eqmc_id')
                ->references('eqmc_id')
                ->on('equipment_categories')
                ->cascadeOnDelete();

            $table->primary(['eqm_id', 'eqmc_id']);
        });

        // 2. Migrate existing category_id data into the pivot table
        DB::table('equipment_units')
            ->whereNotNull('category_id')
            ->orderBy('eqm_id')
            ->each(function ($unit) {
                DB::table('equipment_unit_category')->insertOrIgnore([
                    'eqm_id' => $unit->eqm_id,
                    'eqmc_id' => $unit->category_id,
                ]);
            });

        // 3. Drop the FK constraint first, then the column
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the category_id column with FK
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('equipment_categories', 'eqmc_id')
                ->nullOnDelete();
        });

        // Drop the pivot table
        Schema::dropIfExists('equipment_unit_category');
    }
};

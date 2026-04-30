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
        Schema::table('equipment_categories', function (Blueprint $table) {
            // We add the column after eqmc_name for better visual order in the DB
            $table->unsignedBigInteger('eqmc_parent_id')->nullable()->after('eqmc_name');

            // Define the foreign key pointing back to the same table
            $table->foreign('eqmc_parent_id')
                ->references('eqmc_id')
                ->on('equipment_categories')
                ->onDelete('set null'); // If a parent is deleted, sub-categories become top-level
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_categories', function (Blueprint $table) {
            $table->dropForeign(['eqmc_parent_id']);
            $table->dropColumn('eqmc_parent_id');
        });
    }
};

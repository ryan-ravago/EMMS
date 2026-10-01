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
        Schema::table('asset_tags', function (Blueprint $table) {
            $table->foreignId('asset_parent_id')
                ->nullable()
                ->after('tag')
                ->constrained('equipment_units', 'eqm_id')
                ->nullOnDelete(); // Or ->cascadeOnDelete() depending on your business logic
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_tags', function (Blueprint $table) {
            $table->dropForeign(['asset_parent_id']);
            $table->dropColumn('asset_parent_id');
        });
    }
};

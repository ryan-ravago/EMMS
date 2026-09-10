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
            // New foreign keys
            $table->foreignId('asset_type_id')
                ->nullable()
                ->after('eqm_id')
                ->constrained('asset_types')
                ->restrictOnDelete();

            $table->string('lifecycle_status_id', 10)
                ->nullable()
                ->after('asset_type_id');

            $table->foreign('lifecycle_status_id')
                ->references('status_id')
                ->on('statuses')
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->after('lifecycle_status_id')
                ->constrained('equipment_categories', 'eqmc_id')
                ->nullOnDelete();

            $table->foreignId('location_id')
                ->nullable()
                ->nullable()
                ->after('category_id')
                ->constrained('locations')
                ->nullOnDelete();

            // Other fields
            $table->unsignedSmallInteger('year_model')
                ->nullable()
                ->after('location_id');

            $table->text('specifications')
                ->nullable()
                ->after('year_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropForeign(['lifecycle_status_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['location_id']);

            $table->dropColumn([
                'asset_type_id',
                'lifecycle_status_id',
                'category_id',
                'location_id',
                'year_model',
                'specifications',
            ]);

            $table->string('eqm_eqmt_id')->nullable();
            $table->string('eqm_engine')->nullable();
            $table->string('eqm_vin')->nullable();
        });
    }
};

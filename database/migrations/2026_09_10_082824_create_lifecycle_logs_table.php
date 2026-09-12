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
        Schema::create('lifecycle_logs', function (Blueprint $table) {
            $table->id();

            // Asset
            $table->foreignId('asset_id')
                ->constrained('equipment_units', 'eqm_id')
                ->restrictOnDelete();

            // Action
            $table->string('action_id', 10)
                ->constrained('actions', 'a_id');

            // Result status
            $table->string('status_id', 10)
                ->constrained('statuses', 'status_id');

            // Deployment location
            $table->foreignId('deploy_to_loc_id')
                ->nullable()
                ->constrained('locations', 'id')
                ->restrictOnDelete();

            // Equipment this asset is allocated to
            $table->foreignId('allocate_to_equipment_id')
                ->nullable()
                ->constrained('equipment_units', 'eqm_id')
                ->restrictOnDelete();

            // Additional information
            $table->text('remarks')->nullable();

            // User who performed the action
            // NULL = System
            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('app_users', 'user_id')
                ->nullOnDelete();

            $table->timestamp('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lifecycle_logs');
    }
};

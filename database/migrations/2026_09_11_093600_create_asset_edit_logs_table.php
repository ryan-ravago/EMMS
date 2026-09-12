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
        Schema::create('asset_edit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')
                ->constrained('equipment_units', 'eqm_id')
                ->cascadeOnDelete();
            $table->json('changes');
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
        Schema::dropIfExists('asset_edit_logs');
    }
};

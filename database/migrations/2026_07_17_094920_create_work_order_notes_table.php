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
        Schema::create('work_order_notes', function (Blueprint $table) {
            $table->id('won_id');

            $table->foreignId('won_wo_id')
                ->constrained('work_orders', 'wo_id')
                ->cascadeOnDelete();

            $table->text('won_note');

            // Store multiple attachment paths as JSON
            $table->json('won_attachments')->nullable();

            $table->foreignId('won_created_by')
                ->constrained('app_users', 'user_id')
                ->restrictOnDelete();

            $table->dateTime('won_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_notes');
    }
};

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
        Schema::create('asset_tag_logs', function (Blueprint $table) {
            $table->id();

            $table->dateTime('detected_at');

            $table->foreignId('tag_id')
                ->constrained('asset_tags')
                ->cascadeOnDelete();

            $table->integer('rssi');

            $table->dateTime('created_at');
            $table->dateTime('sent_at');

            $table->foreignId('yard_id')
                ->constrained('locations')
                ->restrictOnDelete();

            $table->enum('status', ['IN', 'OUT']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_tag_logs');
    }
};

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
        Schema::create('sync_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sheet_name');
            $table->integer('synced_rows')->default(0);
            $table->integer('last_row_processed')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->index(['sheet_name', 'synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_audit_logs');
    }
};

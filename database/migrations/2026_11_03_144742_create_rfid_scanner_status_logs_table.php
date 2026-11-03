<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * History of a scanner's status: one row each time it changes (and for the first report), never for repeats,
     * so it stays small. Rows older than rfid.scanner_log_retention_days are pruned.
     */
    public function up(): void
    {
        Schema::create('rfid_scanner_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('yard_id');
            $table->string('scanner_name', 10);
            $table->boolean('is_active');
            $table->dateTime('reported_at');
            $table->timestamp('received_at')->useCurrent();
            $table->unsignedBigInteger('token_id')->nullable();
            $table->string('ip', 45)->nullable();

            $table->index(['yard_id', 'scanner_name', 'id'], 'rfid_scanner_status_logs_scanner_index');
            $table->index('received_at', 'rfid_scanner_status_logs_received_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_scanner_status_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The current state of every gate scanner (one row per yard and scanner), as relayed by the reader server.
     * It never grows: reports only update the row. Changes are kept in rfid_scanner_status_logs.
     */
    public function up(): void
    {
        Schema::create('rfid_scanner_statuses', function (Blueprint $table): void {
            $table->id();
            // No foreign key to locations: a yard change must never block the reader server's reports.
            $table->unsignedBigInteger('yard_id');
            $table->string('scanner_name', 10);
            $table->boolean('is_active');
            // The reader server's own timestamp of the newest report that was applied.
            $table->dateTime('reported_at');
            // When is_active last flipped (reader server time).
            $table->dateTime('status_changed_at');
            // Filled by the database clock on every report, so "no report for a while" does not depend on the reader's clock.
            $table->timestamp('last_seen_at')->useCurrent();

            // Alert bookkeeping (see rfid:check-scanners).
            $table->string('alert_cause', 20)->nullable();
            $table->dateTime('alert_since')->nullable();
            $table->dateTime('last_notified_at')->nullable();

            $table->unique(['yard_id', 'scanner_name'], 'rfid_scanner_statuses_scanner_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_scanner_statuses');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Everything the RFID reader server sends, known tag or not, exactly as it arrived. A background job
     * moves the reads of registered tags into asset_tag_logs; the rest of the rows are deleted after the
     * retention period (rfid.raw_log_retention_days).
     */
    public function up(): void
    {
        Schema::create('rfid_raw_logs', function (Blueprint $table) {
            $table->id();
            $table->char('batch_uuid', 36);
            $table->unsignedBigInteger('yard_id');
            $table->unsignedBigInteger('token_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('tag_id', 64);
            $table->string('status', 3);
            $table->dateTime('detected_at');
            $table->timestamp('received_at')->useCurrent();
            $table->string('outcome', 20)->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('processed_at')->nullable();

            // A reader retrying the same read does not store it twice.
            $table->unique(['yard_id', 'tag_id', 'detected_at', 'status'], 'rfid_raw_logs_dedup_unique');
            $table->index(['outcome', 'id'], 'rfid_raw_logs_outcome_id_index');
            $table->index('received_at', 'rfid_raw_logs_received_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_raw_logs');
    }
};

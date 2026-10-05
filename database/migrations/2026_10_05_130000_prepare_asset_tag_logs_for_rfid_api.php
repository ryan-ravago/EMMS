<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The RFID API sends no rssi and leaves created_at empty, and a unique key
     * lets the API ignore duplicate logs when the sender retries a batch.
     */
    public function up(): void
    {
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->integer('rssi')->nullable()->change();
            $table->dateTime('created_at')->nullable()->change();

            $table->unique(['yard_id', 'tag_id', 'detected_at', 'status'], 'asset_tag_logs_dedup_unique');
        });
    }

    public function down(): void
    {
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->dropUnique('asset_tag_logs_dedup_unique');
        });
    }
};

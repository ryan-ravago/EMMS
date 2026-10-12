<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The RFID API never sends rssi, and created_at was always left empty.
     */
    public function up(): void
    {
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->dropColumn(['rssi', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->integer('rssi')->nullable()->after('tag_id');
            $table->dateTime('created_at')->nullable()->after('rssi');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who assigned / scanned an RFID tag and when (as recorded in AIMS RFIDTbl), and whether it was retired.
     */
    public function up(): void
    {
        Schema::table('asset_tags', function (Blueprint $table) {
            $table->foreignId('assigned_by')
                ->nullable()
                ->after('asset_parent_id')
                ->constrained('app_users', 'user_id')
                ->nullOnDelete();
            $table->dateTime('assigned_date')->nullable()->after('assigned_by');
            $table->dateTime('scanned_date')->nullable()->after('assigned_date');
            $table->foreignId('scanned_by')
                ->nullable()
                ->after('scanned_date')
                ->constrained('app_users', 'user_id')
                ->nullOnDelete();
            $table->boolean('is_deleted')->default(false)->after('scanned_by');
        });
    }

    public function down(): void
    {
        Schema::table('asset_tags', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropConstrainedForeignId('scanned_by');
            $table->dropColumn(['assigned_date', 'scanned_date', 'is_deleted']);
        });
    }
};

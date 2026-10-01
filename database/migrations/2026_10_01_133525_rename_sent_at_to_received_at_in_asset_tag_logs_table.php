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
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->renameColumn('sent_at', 'received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->renameColumn('received_at', 'sent_at');
        });
    }
};

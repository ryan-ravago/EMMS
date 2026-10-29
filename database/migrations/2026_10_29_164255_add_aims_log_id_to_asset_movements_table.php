<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trips copied from AIMS keep the id of the AIMS LOGS row they came from. That makes the import safe to run
     * again (a trip is never copied twice) and lets it be undone (every imported trip has this id, no other has).
     */
    public function up(): void
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('aims_log_id')->nullable()->unique()->after('in_log_id');
        });
    }

    public function down(): void
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            $table->dropUnique(['aims_log_id']);
            $table->dropColumn('aims_log_id');
        });
    }
};

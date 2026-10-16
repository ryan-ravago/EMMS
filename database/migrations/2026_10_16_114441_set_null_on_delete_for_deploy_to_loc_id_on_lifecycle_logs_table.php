<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A location may be deleted even when lifecycle logs point at it: the log keeps
     * deploy_to_loc_name and its deploy_to_loc_id becomes null.
     */
    public function up(): void
    {
        // Logs whose location was already deleted keep their name; clear the dangling id.
        DB::table('lifecycle_logs')
            ->whereNotNull('deploy_to_loc_id')
            ->whereNotExists(fn ($query) => $query
                ->select(DB::raw(1))
                ->from('locations')
                ->whereColumn('locations.id', 'lifecycle_logs.deploy_to_loc_id'))
            ->update(['deploy_to_loc_id' => null]);

        Schema::table('lifecycle_logs', function (Blueprint $table) {
            $table->foreign('deploy_to_loc_id')
                ->references('id')
                ->on('locations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lifecycle_logs', function (Blueprint $table) {
            $table->dropForeign(['deploy_to_loc_id']);
        });
    }
};

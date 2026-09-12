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
        Schema::table('lifecycle_logs', function (Blueprint $table) {
            $table->foreign('action_id')
                ->references('a_id')
                ->on('actions')
                ->restrictOnDelete();
            $table->foreign('status_id')
                ->references('status_id')
                ->on('statuses')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lifecycle_logs', function (Blueprint $table) {
            $table->dropForeign(['action_id']);
            $table->dropForeign(['status_id']);
        });
    }
};

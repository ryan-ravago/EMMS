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
        Schema::table('inspection_items', function (Blueprint $table) {
            // 1. Create the VARCHAR(10) column
            $table->string('insi_status_id', 10)->after('insi_task_id');

            // 2. Define it as a foreign key referencing the 'statuses' table
            $table->foreign('insi_status_id')
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
        Schema::table('inspection_items', function (Blueprint $table) {
            // Drop the foreign key constraint first, then the column
            $table->dropForeign(['insi_status_id']);
            $table->dropColumn('insi_status_id');
        });
    }
};

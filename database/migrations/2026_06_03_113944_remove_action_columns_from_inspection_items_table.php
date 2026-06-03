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
            $table->dropForeign(['insi_a_id']);
            $table->dropColumn(['insi_a_id', 'insi_action_made']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_items', function (Blueprint $table) {
            $table->string('insi_a_id', 10)->nullable();
            $table->string('insi_action_made')->nullable();
            $table->foreign('insi_a_id')->references('a_id')->on('actions')->restrictOnDelete();
        });
    }
};

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
        Schema::table('inspection_item_logs', function (Blueprint $table) {
            $table->string('inil_a_id', 10)->nullable()->after('inil_insi_id');
            $table->string('inil_status_id', 10)->nullable()->after('inil_a_id');

            $table->foreign('inil_a_id')
                ->references('a_id')
                ->on('actions')
                ->restrictOnDelete();

            $table->foreign('inil_status_id')
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
        Schema::table('inspection_item_logs', function (Blueprint $table) {
            $table->dropForeign(['inil_a_id']);
            $table->dropForeign(['inil_status_id']);
            $table->dropColumn(['inil_a_id', 'inil_status_id']);
        });
    }
};

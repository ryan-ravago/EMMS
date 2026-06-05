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
            $table->unsignedBigInteger('inil_wo_id')->nullable()->after('inil_remarks');
            $table->foreign('inil_wo_id', 'inspection_item_logs_inil_wo_id_foreign')
                ->references('wo_id')
                ->on('work_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_item_logs', function (Blueprint $table) {
            $table->dropForeign('inspection_item_logs_inil_wo_id_foreign');
            $table->dropColumn('inil_wo_id');
        });
    }
};

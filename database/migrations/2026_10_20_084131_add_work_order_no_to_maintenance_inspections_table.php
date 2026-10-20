<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The work order raised from this inspection (AppSheet column insp_wo_id), stored as its work order number.
     * It points at work_orders.wo_no like a foreign key would, but is deliberately not one: the inspection is a
     * permanent text record and must keep the number even if the work order is deleted.
     */
    public function up(): void
    {
        Schema::table('maintenance_inspections', function (Blueprint $table) {
            $table->string('work_order_no')->nullable()->index()->after('checklist_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_inspections', function (Blueprint $table) {
            $table->dropIndex(['work_order_no']);
            $table->dropColumn('work_order_no');
        });
    }
};

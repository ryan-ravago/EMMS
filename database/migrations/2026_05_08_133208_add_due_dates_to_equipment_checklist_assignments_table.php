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
        Schema::table('equipment_checklist_assignments', function (Blueprint $table) {
            $table->datetime('eca_due_effectivity_dt')->nullable()->after('eca_eqm_id');
            $table->datetime('eca_due_dt')->nullable()->after('eca_due_effectivity_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_checklist_assignments', function (Blueprint $table) {
            $table->dropColumn(['eca_due_effectivity_dt', 'eca_due_dt']);
        });
    }
};

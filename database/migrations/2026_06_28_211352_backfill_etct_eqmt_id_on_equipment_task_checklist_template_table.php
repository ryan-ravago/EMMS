<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('equipment_task_checklist_template as etct')
            ->join('equipment_units as eqm', 'eqm.eqm_id', '=', 'etct.etct_eqm_id')
            ->join('equipment_models as eqmm', 'eqmm.eqmm_id', '=', 'eqm.eqm_eqmm_id')
            ->whereNull('etct.etct_eqmt_id')
            ->update([
                'etct.etct_eqmt_id' => DB::raw('eqmm.eqmm_eqmt_id'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('equipment_task_checklist_template')
            ->update(['etct_eqmt_id' => null]);
    }
};

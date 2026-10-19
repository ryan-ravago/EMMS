<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Copies the old insps / insp_items rows (every one of them was a submitted inspection) into the text-based
     * maintenance inspection tables. Department and equipment ids are turned into text here. Inspections whose
     * inspection number already exists are skipped, so running this twice is harmless.
     */
    public function up(): void
    {
        if (! Schema::hasTable('insps') || ! Schema::hasTable('maintenance_inspections')) {
            return;
        }

        DB::table('insps as i')
            ->leftJoin('departments as d', 'd.dep_id', '=', 'i.insp_dep_id')
            ->leftJoin('equipment_units as e', 'e.eqm_id', '=', 'i.insp_eqm_id')
            ->orderBy('i.insp_id')
            ->select([
                'i.insp_id', 'i.insp_no', 'i.checklist_template_name', 'i.insp_remarks', 'i.insp_by',
                'i.insp_submitted_by', 'i.insp_submitted_at',
                'd.dep_code', 'd.dep_name', 'e.eqm_prc_code', 'e.eqm_name',
            ])
            ->each(function (object $legacy): void {
                $inspectionNo = $legacy->insp_no ?: 'LEGACY-'.$legacy->insp_id;

                if (DB::table('maintenance_inspections')->where('inspection_no', $inspectionNo)->exists()) {
                    return;
                }

                $newId = DB::table('maintenance_inspections')->insertGetId([
                    'inspection_no' => $inspectionNo,
                    'status' => 'Completed',
                    'department_code' => $legacy->dep_code,
                    'department_name' => $legacy->dep_name,
                    'equipment_prc_code' => $legacy->eqm_prc_code,
                    'equipment_name' => $legacy->eqm_name,
                    'checklist_name' => $legacy->checklist_template_name,
                    'technicians' => $legacy->insp_by,
                    'tech_action' => 'Submitted',
                    'tech_action_by' => $legacy->insp_submitted_by,
                    'tech_remarks' => $legacy->insp_remarks,
                    'closed_at' => $legacy->insp_submitted_at,
                ]);

                $items = DB::table('insp_items')
                    ->where('inspi_insp_id', $legacy->insp_id)
                    ->orderBy('inspi_id')
                    ->get(['inspi_task', 'inspi_result', 'inspi_remarks'])
                    ->map(fn (object $item): array => [
                        'maintenance_inspection_id' => $newId,
                        'task' => $item->inspi_task,
                        'result' => $item->inspi_result,
                        'remarks' => $item->inspi_remarks,
                    ])
                    ->all();

                if ($items !== []) {
                    DB::table('maintenance_inspection_items')->insert($items);
                }
            });
    }

    /**
     * The copied rows are left in place: they cannot be told apart from inspections synced later.
     */
    public function down(): void
    {
        //
    }
};

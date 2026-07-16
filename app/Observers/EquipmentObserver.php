<?php

namespace App\Observers;

use App\Models\Equipment;

class EquipmentObserver
{
    /**
     * Handle the Equipment "created" event.
     */
    public function created(Equipment $equipment): void
    {
        //
    }

    /**
     * Handle the Equipment "updated" event.
     */
    public function updated(Equipment $equipment): void
    {
        //
    }

    public function saving(Equipment $equipment): void
    {
        // Recalculate next due whenever PM intervals change
        if ($equipment->isDirty([
            'eqm_pm_itrv_years',
            'eqm_pm_itrv_months',
            'eqm_pm_itrv_weeks',
            'eqm_pm_itrv_days',
            'eqm_pm_itrv_start_date',
            'eqm_last_pm_notified_at',
        ])) {
            $equipment->eqm_next_pm_due_at = $equipment->calculateNextDueDate();
        }
    }

    /**
     * Handle the Equipment "deleted" event.
     */
    public function deleted(Equipment $equipment): void
    {
        //
    }

    /**
     * Handle the Equipment "restored" event.
     */
    public function restored(Equipment $equipment): void
    {
        //
    }

    /**
     * Handle the Equipment "force deleted" event.
     */
    public function forceDeleted(Equipment $equipment): void
    {
        //
    }
}

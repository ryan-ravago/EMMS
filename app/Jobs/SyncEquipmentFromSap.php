<?php

namespace App\Jobs;

use App\Models\AppSetting;
use App\Models\Equipment;
use App\Models\OPRC;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEquipmentFromSap implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(): void
    {
        DB::transaction(function () {
            $sapRecords = OPRC::select([
                'PrcCode',
                'PrcName',
                'Active',
            ])->get()
                ->filter(fn ($sap) => ! empty($sap->PrcCode))
                ->unique('PrcCode')
                ->keyBy('PrcCode');

            if ($sapRecords->isEmpty()) {
                Notification::make()
                    ->title('No Records Found')
                    ->body('SAP returned no records to sync.')
                    ->warning()
                    ->send();

                return;
            }

            $localEquipment = Equipment::whereNotNull('eqm_prc_code')->get()->keyBy('eqm_prc_code');

            $deactivated = 0;

            // Deactivate local records whose PRC code is no longer present in SAP.
            foreach ($localEquipment as $prcCode => $equipment) {
                if ($sapRecords->has($prcCode) || ! $equipment->eqm_is_active) {
                    continue;
                }

                $equipment->eqm_is_active = false;
                $equipment->save();
                $deactivated++;
            }

            $synced = 0;

            // Route through Eloquent (not a bulk upsert) so updates fire model events and get
            // audited via Equipment::save(), with a null performed_by since this runs in a job.
            foreach ($sapRecords as $prcCode => $sap) {
                $equipment = $localEquipment->get($prcCode) ?? new Equipment(['eqm_prc_code' => $prcCode]);

                $equipment->eqm_name = $sap->PrcName;
                $equipment->eqm_is_active = $sap->Active === 'Y';

                if ($equipment->exists && ! $equipment->isDirty()) {
                    continue;
                }

                $equipment->save();
                $synced++;
            }

            AppSetting::where('key', 'sap_sync_time')
                ->update([
                    'last_equipment_sync' => now(),
                ]);

            Notification::make()
                ->title('SAP Sync Complete')
                ->body("Synced: {$synced} records. Deactivated: {$deactivated} records.")
                ->success()
                ->send();
        });
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SAP Sync Job failed: '.$e->getMessage());
    }
}

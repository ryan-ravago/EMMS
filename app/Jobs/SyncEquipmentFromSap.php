<?php

namespace App\Jobs;

use App\Models\AppSetting;
use App\Models\Equipment;
use App\Models\OPRC;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
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
            ])->get();

            if ($sapRecords->isEmpty()) {
                Notification::make()
                    ->title('No Records Found')
                    ->body('SAP returned no records to sync.')
                    ->warning()
                    ->send();
                return;
            }

            $data = $sapRecords
                ->filter(fn($sap) => !empty($sap->PrcCode))
                ->map(fn($sap) => [
                    'eqm_prc_code'  => $sap->PrcCode,
                    'eqm_name'      => $sap->PrcName,
                    'eqm_is_active' => $sap->Active === 'Y' ? 1 : 0,
                ])->toArray();

            // 👇 get all PrcCodes from SAP
            $sapPrcCodes = $sapRecords
                ->filter(fn($sap) => !empty($sap->PrcCode))
                ->pluck('PrcCode')
                ->toArray();

            // 👇 deactivate local records not found in SAP
            $deactivated = Equipment::whereNotNull('eqm_prc_code')
                ->whereNotIn('eqm_prc_code', $sapPrcCodes)
                ->where('eqm_is_active', 1)
                ->update(['eqm_is_active' => 0]);

            Equipment::upsert(
                $data,
                ['eqm_prc_code'],
                ['eqm_name', 'eqm_is_active']
            );

            AppSetting::where('key', 'sap_sync_time')
                ->update([
                    'last_equipment_sync' => now(),
                ]);

            Notification::make()
                ->title('SAP Sync Complete')
                ->body("Synced: " . count($data) . " records. Deactivated: {$deactivated} records.")
                ->success()
                ->send();
        });
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SAP Sync Job failed: ' . $e->getMessage());
    }
}

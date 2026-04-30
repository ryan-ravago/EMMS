<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use App\Models\OPRC;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListEquipment extends ListRecords
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('sync')
                ->label('Sync from SAP')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sync Equipment from SAP')
                ->modalDescription('This will fetch equipment data from SAP and update your local database.')
                ->modalSubmitActionLabel('Yes, Sync Now')
                ->closeModalByClickingAway(false)        // 👈 can't close by clicking outside
                ->closeModalByEscaping(false)            // 👈 can't close by pressing Escape
                ->action(function () {
                    try {
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

                            Notification::make()
                                ->title('SAP Sync Complete')
                                ->body("Synced: " . count($data) . " records. Deactivated: {$deactivated} records.")
                                ->success()
                                ->send();
                        });
                    } catch (\Illuminate\Database\QueryException $e) {
                        $previous = $e->getPrevious();

                        if ($previous instanceof \PDOException) {
                            Notification::make()
                                ->title('SAP Connection Failed')
                                ->body('Could not connect to SAP database. Please check your network or server.')
                                ->danger()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Database Error')
                                ->body('Query failed: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync Failed')
                            ->body('Unexpected error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
            // ->successNotificationTitle('Sync completed successfully'),
        ];
    }
}

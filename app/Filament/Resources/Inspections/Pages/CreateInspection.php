<?php

namespace App\Filament\Resources\Inspections\Pages;

use App\Filament\Resources\Inspections\InspectionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateInspection extends CreateRecord
{
    protected static string $resource = InspectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!auth()->user()->hasRole('super_admin')) {
            $data['ins_dep_id'] = auth()->user()->user_dep_id;
        }

        $data['ins_submitted_by'] = auth()->id();
        $data['ins_submitted_dt'] = now();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['inspection_items'] ?? [];
        unset($data['inspection_items']);

        if (empty($items)) {
            Notification::make()
                ->title('No inspection items found')
                ->body('Please ensure the equipment has a checklist template configured.')
                ->danger()
                ->send();

            $this->halt();
        }

        return DB::transaction(function () use ($data, $items) {
            try {
                $inspection = static::getModel()::create($data);

                $inspection->inspectionItems()->createMany(
                    collect($items)->map(fn($item) => [
                        'insi_task_id'             => $item['insi_task_id'],
                        'insi_cli_name_for_record' => $item['insi_cli_name_for_record'],
                        'insi_result'              => $item['insi_result'],
                        'insi_remarks'             => $item['insi_remarks'] ?? null,
                    ])->toArray()
                );

                return $inspection;
            } catch (\Exception $e) {
                Log::error('Inspection creation failed', [
                    'error' => $e->getMessage(),
                    'data'  => $data,
                ]);

                Notification::make()
                    ->title('Failed to save inspection')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();

                $this->halt();
            }
        });
    }
}

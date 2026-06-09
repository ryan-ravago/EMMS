<?php

namespace App\Filament\Resources\Inspections\Pages;

use App\Filament\Resources\Inspections\InspectionResource;
use App\Mail\InspectionConductedMail;
use App\Models\Action;
use App\Models\AppUser;
use App\Models\InspectionItemLog;
use App\Models\Status;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateInspection extends CreateRecord
{
    protected ?Carbon $now = null;

    protected function now(): Carbon
    {
        return $this->now ??= now();
    }

    protected static string $resource = InspectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! auth()->user()->hasRole('super_admin')) {
            $data['ins_dep_id'] = auth()->user()->user_dep_id;
        }

        $data['ins_submitted_by'] = auth()->id();
        $data['ins_submitted_dt'] = $this->now();

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

                $action = Action::find('create');
                $statusPnd = Status::find('pnd');
                $statusCmp = Status::find('cmp');

                $createdItems = $inspection->inspectionItems()->createMany(
                    collect($items)->map(fn ($item) => [
                        'insi_task_id' => $item['insi_task_id'],
                        'insi_status_id' => in_array($item['insi_result'], ['P', 'N']) ? 'cmp' : 'pnd',
                        'insi_cli_name_for_record' => $item['insi_cli_name_for_record'],
                        'insi_result' => $item['insi_result'],
                        'insi_remarks' => $item['insi_remarks'] ?? null,
                        'insi_closed_dt' => in_array($item['insi_result'], ['P', 'N']) ? $this->now() : null,
                    ])->toArray()
                );

                $logs = $createdItems->map(fn ($item) => [
                    'inil_insi_id' => $item->insi_id,
                    'inil_a_id' => $action->a_id,
                    'inil_status_id' => $item->insi_status_id === 'cmp' ? $statusCmp->status_id : $statusPnd->status_id,
                    'inil_action_made' => $action->a_past_tense,
                    'inil_status_log' => $item->insi_status_id === 'cmp' ? $statusCmp->status_title : $statusPnd->status_title,
                    'inil_remarks' => null,
                    'inil_by' => auth()->id(),
                    'inil_dt' => $this->now(),
                ])->toArray();

                InspectionItemLog::insert($logs);

                return $inspection;
            } catch (\Exception $e) {
                Log::error('Inspection creation failed', [
                    'error' => $e->getMessage(),
                    'data' => $data,
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

    protected function afterCreate(): void
    {
        $inspection = $this->record;
        $inspection->load(['equipment', 'conductedBy', 'inspectionItems', 'department']);

        $hasFailedItems = $inspection->inspectionItems->contains(
            fn ($item) => $item->insi_result === 'F'
        );

        $failedItems = $inspection->inspectionItems->filter(
            fn ($item) => $item->insi_result === 'F'
        );

        $conductor = $inspection->conductedBy;

        // 1. Notify technician (conductor) — Confirmation
        if ($conductor?->user_email) {
            Mail::to($conductor->user_email)
                ->queue(new InspectionConductedMail(
                    inspection: $inspection,
                    recipientType: 'technician',
                    hasFailedItems: $hasFailedItems,
                    failedItems: $failedItems,
                ));
        }

        // 2. Notify managers of same department — Update
        $managers = AppUser::whereHas('roles', fn ($q) => $q->where('name', 'manager'))
            ->where('user_dep_id', $inspection->ins_dep_id)
            ->get();

        foreach ($managers as $manager) {
            if ($manager->user_email) {
                Mail::to($manager->user_email)
                    ->queue(new InspectionConductedMail(
                        inspection: $inspection,
                        recipientType: 'manager',
                        hasFailedItems: $hasFailedItems,
                        failedItems: $failedItems,
                    ));
            }
        }
    }
}

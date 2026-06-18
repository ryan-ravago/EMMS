<?php

namespace App\Filament\Resources\RequestorWorkOrders\Pages;

use App\Filament\Resources\RequestorWorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use App\Mail\WorkOrderCancellationMail;
use App\Models\Action as ModelsAction;
use App\Models\InspectionItem;
use App\Models\InspectionItemLog;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceTaskLog;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ViewRequestorWorkOrder extends ViewRecord
{
    protected static string $resource = RequestorWorkOrderResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): string
    {
        return 'Details';
    }

    public function getRelationManagers(): array
    {
        return [
            LogsRelationManager::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Cancel Work Order')
                ->visible(fn () => auth()->user()->can('cancelWorkOrder', $this->record))
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->modalHeading('Cancel Work Order')
                ->modalWidth(Width::Large)
                ->closeModalByClickingAway(false)
                ->modalCloseButton(false)
                ->schema([
                    Textarea::make('wol_note')
                        ->label('Reason for Cancellation')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data, WorkOrder $record) {
                    try {
                        DB::transaction(function () use ($data, $record) {
                            $now = now();
                            $workOrder = WorkOrder::where('wo_id', $record->wo_id)
                                ->lockForUpdate()
                                ->first();

                            if ($workOrder->wo_status_id !== 'pnd') {
                                throw new \Exception('Work order cannot be cancelled at its current status.');
                            }

                            $action = ModelsAction::find('cancel');
                            $status = Status::find('cnc');

                            WorkOrderLog::create([
                                'wol_wo_id' => $workOrder->wo_id,
                                'wol_a_id' => $action->a_id,
                                'wol_status_id' => $status->status_id,
                                'wol_a_log' => $action->a_past_tense,
                                'wol_status_log' => $status->status_title,
                                'wol_note' => $data['wol_note'],
                                'wol_by' => auth()->id(),
                                'wol_dt' => $now,
                            ]);

                            if ($workOrder->wo_insi_id) {
                                $inspectionItem = InspectionItem::where('insi_id', $workOrder->wo_insi_id)
                                    ->lockForUpdate()
                                    ->first();

                                if ($inspectionItem) {
                                    $inspectionItem->update([
                                        'insi_status_id' => 'pnd',
                                    ]);

                                    $updateAction = ModelsAction::find('upt');
                                    $pendingStatus = Status::find('pnd');

                                    InspectionItemLog::create([
                                        'inil_insi_id' => $inspectionItem->insi_id,
                                        'inil_a_id' => $updateAction->a_id,
                                        'inil_status_id' => $pendingStatus->status_id,
                                        'inil_action_made' => $updateAction->a_past_tense,
                                        'inil_status_log' => $pendingStatus->status_title,
                                        'inil_remarks' => 'Cancelled the linked work order and reverted this inspection finding to pending.',
                                        'inil_wo_id' => $workOrder->wo_id,
                                        'inil_by' => auth()->id(),
                                        'inil_dt' => $now,
                                    ]);
                                }
                            } elseif ($workOrder->wo_mt_id) {
                                $maintenanceTask = MaintenanceTask::where('mt_id', $workOrder->wo_mt_id)
                                    ->lockForUpdate()
                                    ->first();

                                if ($maintenanceTask) {
                                    $maintenanceTask->update([
                                        'mt_status_id' => 'pnd',
                                    ]);

                                    $updateAction = ModelsAction::find('upt');
                                    $pendingStatus = Status::find('pnd');

                                    MaintenanceTaskLog::create([
                                        'mtl_mt_id' => $maintenanceTask->mt_id,
                                        'mtl_status_id' => $pendingStatus->status_id,
                                        'mtl_due_dt' => $maintenanceTask->mt_due_dt,
                                        'mtl_last_act_made' => $updateAction->a_id,
                                        'mtl_wo_id' => $workOrder->wo_id,
                                        'mtl_remarks' => 'Cancelled the linked work order and reverted this maintenance task finding to pending.',
                                        'mtl_by' => auth()->id(),
                                        'mtl_dt' => $now,
                                    ]);
                                }
                            }

                            $workOrder->update([
                                'wo_status_id' => $status->status_id,
                                'wo_closed_dt' => $now,
                            ]);

                            activity()
                                ->performedOn($workOrder)
                                ->useLog('WorkOrder')
                                ->event($action->a_id)
                                ->withProperties([
                                    'action_id' => $action->a_id,
                                    'status_id' => $status->status_id,
                                    'note' => $data['wol_note'],
                                ])
                                ->log($action->a_past_tense);
                        });

                        $record->load(['workers', 'createdBy', 'priority']);
                        $canceller = auth()->user();
                        $note = $data['wol_note'];

                        // 1. Notify requestor (canceller) — Confirmation
                        Mail::to($canceller->user_email)
                            ->queue(new WorkOrderCancellationMail(
                                workOrder: $record,
                                canceller: $canceller,
                                reason: $note,
                                recipientType: 'requestor',
                            ));

                        // 2. Notify manager — Update
                        $managers = \App\Models\AppUser::whereHas('roles', fn ($q) => $q->where('name', 'manager'))
                            ->where('user_dep_id', $record->wo_dep_id)
                            ->get();

                        foreach ($managers as $manager) {
                            if ($manager->user_email && $manager->user_id !== $canceller->user_id) {
                                Mail::to($manager->user_email)
                                    ->queue(new WorkOrderCancellationMail(
                                        workOrder: $record,
                                        canceller: $canceller,
                                        reason: $note,
                                        recipientType: 'manager',
                                    ));
                            }
                        }

                        // 3. Notify creator if not the canceller
                        if ($record->createdBy && $record->createdBy->user_email && $record->createdBy->user_id !== $canceller->user_id) {
                            Mail::to($record->createdBy->user_email)
                                ->queue(new WorkOrderCancellationMail(
                                    workOrder: $record,
                                    canceller: $canceller,
                                    reason: $note,
                                    recipientType: 'manager',
                                ));
                        }

                        Notification::make()
                            ->title('Work order cancelled.')
                            ->success()
                            ->send();

                        $this->redirect(RequestorWorkOrderResource::getUrl('view', ['record' => $record->wo_id]), navigate: true);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to cancel work order.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

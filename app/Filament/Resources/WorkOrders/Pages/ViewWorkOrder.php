<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\WorkOrders\RelationManagers\LogUpdatesRelationManager;
use App\Filament\Resources\WorkOrders\RelationManagers\ReportSubmissionsRelationManager;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Mail\WorkOrderApprovalMail;
use App\Mail\WorkOrderCancellationMail;
use App\Mail\WorkOrderRejectionMail;
use App\Models\Action as ModelsAction;
use App\Models\InspectionItem;
use App\Models\InspectionItemLog;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceTaskLog;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use App\Models\WorkOrderLogUpdate;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ViewWorkOrder extends ViewRecord
{
    protected static string $resource = WorkOrderResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true; // merges infolist + relation manager tabs together
    }

    public function getContentTabLabel(): string
    {
        return 'Details'; // label for the main infolist tab
    }

    public function getRelationManagers(): array
    {
        return [
            LogsRelationManager::class,
            LogUpdatesRelationManager::class,
            ReportSubmissionsRelationManager::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ActionGroup::make([
                Action::make('addUpdate')
                    ->label('Add Update')
                    ->visible(fn() => Auth::user()->can('addUpdate', $this->record))
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->modalHeading('Add Work Order Update')
                    ->modalWidth(Width::TwoExtraLarge)
                    ->closeModalByClickingAway(false)
                    ->modalCloseButton(false)
                    ->schema([
                        Textarea::make('wolu_update_note')
                            ->label('Update Note')
                            ->required()
                            ->rows(4),
                        FileUpload::make('wolu_attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->nullable()
                            ->maxFiles(10)
                            ->maxSize(10240)
                            ->acceptedFileTypes([
                                'image/*',
                                'application/pdf',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                            ])
                            ->imageEditor(),
                    ])
                    ->action(function (array $data, WorkOrder $record) {
                        WorkOrderLogUpdate::create([
                            'wolu_wo_id' => $record->wo_id,
                            'wolu_update_note' => $data['wolu_update_note'],
                            'wolu_attachments' => $data['wolu_attachments'] ?? null,
                            'wolu_by' => auth()->id(),
                            'wolu_dt' => now(),
                        ]);

                        Notification::make()
                            ->title('Update added successfully.')
                            ->success()
                            ->send();

                        $this->js('Livewire.dispatch("refreshRelationManager")');
                    }),
                Action::make('reject')
                    ->label('Reject Completion')
                    ->visible(fn() => Auth::user()->can('rejectCompletion', $this->record))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Completion Request')
                    ->modalWidth(Width::Large)
                    ->closeModalByClickingAway(false)
                    ->modalCloseButton(false)
                    ->schema([
                        Textarea::make('wol_note')
                            ->label('Reason for Rejection')
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

                                if ($workOrder->wo_status_id !== 'pca') {
                                    throw new \Exception('Work order is not pending completion approval.');
                                }

                                $action = ModelsAction::firstWhere('a_id', 'reject');
                                $status = Status::firstWhere('status_id', 'rej');
                                $pendingStatus = Status::firstWhere('status_id', 'pnd');

                                WorkOrderLog::create([
                                    'wol_wo_id' => $workOrder->wo_id,
                                    'wol_a_id' => $action->a_id,
                                    'wol_status_id' => $status->status_id,
                                    'wol_a_log' => $action->a_past_tense,
                                    'wol_status_log' => $status->status_title,
                                    'wol_note' => $data['wol_note'],
                                    'wol_by' => Auth::id(),
                                    'wol_dt' => $now,
                                ]);

                                if ($workOrder->wo_insi_id) {
                                    $inspectionItem = InspectionItem::query()
                                        ->where('insi_id', $workOrder->wo_insi_id)
                                        ->lockForUpdate()
                                        ->first();

                                    if ($inspectionItem) {
                                        $inspectionItem->update([
                                            'insi_status_id' => $pendingStatus->status_id,
                                        ]);

                                        InspectionItemLog::create([
                                            'inil_insi_id' => $inspectionItem->insi_id,
                                            'inil_a_id' => $action->a_id,
                                            'inil_status_id' => $pendingStatus->status_id,
                                            'inil_action_made' => $action->a_past_tense,
                                            'inil_status_log' => $pendingStatus->status_title,
                                            'inil_remarks' => 'Completion request rejected; inspection finding moved back to pending.',
                                            'inil_wo_id' => $workOrder->wo_id,
                                            'inil_by' => Auth::id(),
                                            'inil_dt' => $now,
                                        ]);
                                    }
                                } elseif ($workOrder->wo_mt_id) {
                                    $maintenanceTask = MaintenanceTask::query()
                                        ->where('mt_id', $workOrder->wo_mt_id)
                                        ->lockForUpdate()
                                        ->first();

                                    if ($maintenanceTask) {
                                        $maintenanceTask->update([
                                            'mt_status_id' => $pendingStatus->status_id,
                                        ]);

                                        MaintenanceTaskLog::create([
                                            'mtl_mt_id' => $maintenanceTask->mt_id,
                                            'mtl_status_id' => $pendingStatus->status_id,
                                            'mtl_due_dt' => $maintenanceTask->mt_due_dt,
                                            'mtl_last_act_made' => $action->a_id,
                                            'mtl_wo_id' => $workOrder->wo_id,
                                            'mtl_remarks' => 'Completion request rejected; maintenance task moved back to pending.',
                                            'mtl_by' => Auth::id(),
                                            'mtl_dt' => $now,
                                        ]);
                                    }
                                }

                                $workOrder->update([
                                    'wo_status_id' => $status->status_id,
                                    'wo_closed_dt' => $now,
                                ]);
                            });

                            $record->load(['workers', 'createdBy', 'priority']);
                            $rejector = auth()->user();
                            $note = $data['wol_note'];

                            // 1. Notify manager (rejector) — Confirmation
                            Mail::to($rejector->user_email)
                                ->queue(new WorkOrderRejectionMail(
                                    workOrder: $record,
                                    rejector: $rejector,
                                    reason: $note,
                                    recipientType: 'manager',
                                ));

                            // 2. Notify all assigned technicians (requestor + groupmates) — Update
                            foreach ($record->workers as $technician) {
                                if ($technician->user_email) {
                                    Mail::to($technician->user_email)
                                        ->queue(new WorkOrderRejectionMail(
                                            workOrder: $record,
                                            rejector: $rejector,
                                            reason: $note,
                                            recipientType: 'technician',
                                        ));
                                }
                            }

                            Notification::make()
                                ->title('Completion request rejected.')
                                ->success()
                                ->send();

                            $this->redirect(WorkOrderResource::getUrl('view', ['record' => $record->wo_id]), navigate: true);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to reject completion request.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('approve')
                    ->label('Approve Completion')
                    ->visible(fn() => Auth::user()->can('approveCompletion', $this->record))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->modalHeading('Approve Completion Request')
                    ->modalWidth(Width::Large)
                    ->closeModalByClickingAway(false)
                    ->modalCloseButton(false)
                    ->schema([
                        Textarea::make('wol_note')
                            ->label('Note')
                            ->rows(4),
                    ])
                    ->action(function (array $data, WorkOrder $record) {
                        try {
                            DB::transaction(function () use ($data, $record) {
                                $now = now();

                                $workOrder = WorkOrder::where('wo_id', $record->wo_id)
                                    ->lockForUpdate()
                                    ->first();

                                if ($workOrder->wo_status_id !== 'pca') {
                                    throw new \Exception('Work order is not pending completion approval.');
                                }

                                $action = ModelsAction::find('approve');
                                $status = Status::find('cmp');

                                WorkOrderLog::create([
                                    'wol_wo_id' => $workOrder->wo_id,
                                    'wol_a_id' => $action->a_id,
                                    'wol_status_id' => $status->status_id,
                                    'wol_a_log' => $action->a_past_tense,
                                    'wol_status_log' => $status->status_title,
                                    'wol_note' => $data['wol_note'] ?? null,
                                    'wol_by' => auth()->id(),
                                    'wol_dt' => $now,
                                ]);

                                if ($workOrder->wo_insi_id) {
                                    $inspectionItem = InspectionItem::where('insi_id', $workOrder->wo_insi_id)
                                        ->lockForUpdate()
                                        ->first();

                                    if ($inspectionItem) {
                                        $inspectionItem->update([
                                            'insi_status_id' => $status->status_id,
                                        ]);

                                        InspectionItemLog::create([
                                            'inil_insi_id' => $inspectionItem->insi_id,
                                            'inil_a_id' => $action->a_id,
                                            'inil_status_id' => $status->status_id,
                                            'inil_action_made' => $action->a_past_tense,
                                            'inil_status_log' => $status->status_title,
                                            'inil_remarks' => 'Completion approved by manager.',
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
                                            'mt_status_id' => $status->status_id,
                                        ]);

                                        MaintenanceTaskLog::create([
                                            'mtl_mt_id' => $maintenanceTask->mt_id,
                                            'mtl_status_id' => $status->status_id,
                                            'mtl_due_dt' => $maintenanceTask->mt_due_dt,
                                            'mtl_last_act_made' => $action->a_id,
                                            'mtl_wo_id' => $workOrder->wo_id,
                                            'mtl_remarks' => 'Completion approved by manager.',
                                            'mtl_by' => auth()->id(),
                                            'mtl_dt' => $now,
                                        ]);
                                    }
                                }

                                $workOrder->update([
                                    'wo_status_id' => $status->status_id,
                                    'wo_closed_dt' => $now,
                                ]);
                            });

                            $record->load(['workers', 'createdBy', 'priority']);
                            $approver = auth()->user();
                            $note = $data['wol_note'] ?? null;

                            // 1. Notify manager (approver) — Confirmation
                            Mail::to($approver->user_email)
                                ->queue(new WorkOrderApprovalMail(
                                    workOrder: $record,
                                    approver: $approver,
                                    note: $note,
                                    recipientType: 'manager',
                                ));

                            // 2. Notify all assigned technicians (requestor + groupmates) — Update
                            foreach ($record->workers as $technician) {
                                if ($technician->user_email) {
                                    Mail::to($technician->user_email)
                                        ->queue(new WorkOrderApprovalMail(
                                            workOrder: $record,
                                            approver: $approver,
                                            note: $note,
                                            recipientType: 'technician',
                                        ));
                                }
                            }

                            Notification::make()
                                ->title('Completion approved successfully.')
                                ->success()
                                ->send();

                            $this->redirect(WorkOrderResource::getUrl('view', ['record' => $record->wo_id]), navigate: true);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to approve completion request.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('cancel')
                    ->label('Cancel Work Order')
                    ->visible(fn() => Auth::user()->can('cancelWorkOrder', $this->record))
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

                                if (! in_array($workOrder->wo_status_id, ['inprog', 'pca'])) {
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
                            });

                            $record->load(['workers', 'createdBy', 'priority']);
                            $canceller = auth()->user();
                            $note = $data['wol_note'];

                            // 1. Notify manager (canceller) — Confirmation
                            Mail::to($canceller->user_email)
                                ->queue(new WorkOrderCancellationMail(
                                    workOrder: $record,
                                    canceller: $canceller,
                                    reason: $note,
                                    recipientType: 'manager',
                                ));

                            // 2. Notify all assigned technicians — Update
                            foreach ($record->workers as $technician) {
                                if ($technician->user_email) {
                                    Mail::to($technician->user_email)
                                        ->queue(new WorkOrderCancellationMail(
                                            workOrder: $record,
                                            canceller: $canceller,
                                            reason: $note,
                                            recipientType: 'technician',
                                        ));
                                }
                            }

                            Notification::make()
                                ->title('Work order cancelled.')
                                ->success()
                                ->send();

                            $this->redirect(WorkOrderResource::getUrl('view', ['record' => $record->wo_id]), navigate: true);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to cancel work order.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

            ])
                ->label('Actions')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('gray')
                ->button(),
        ];
    }
}

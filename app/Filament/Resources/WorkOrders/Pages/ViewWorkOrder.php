<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\WorkOrders\RelationManagers\LogUpdatesRelationManager;
use App\Filament\Resources\WorkOrders\RelationManagers\ReportSubmissionsRelationManager;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Mail\WorkOrderApprovalMail;
use App\Mail\WorkOrderApprovedMail;
use App\Mail\WorkOrderAssignedMail;
use App\Mail\WorkOrderCancellationMail;
use App\Mail\WorkOrderManagerCancellationConfirmationMail;
use App\Mail\WorkOrderRejectedMail;
use App\Mail\WorkOrderRejectionMail;
use App\Models\Action as ModelsAction;
use App\Models\AppUser;
use App\Models\InspectionItem;
use App\Models\InspectionItemLog;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceTaskLog;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use App\Models\WorkOrderLogUpdate;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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

    // public function hasCombinedRelationManagerTabsWithContent(): bool
    // {
    //     return true; // merges infolist + relation manager tabs together
    // }

    // public function getContentTabLabel(): string
    // {
    //     return 'Details'; // label for the main infolist tab
    // }

    // public function getRelationManagers(): array
    // {
    //     return [
    //         LogsRelationManager::class,
    //         LogUpdatesRelationManager::class,
    //         ReportSubmissionsRelationManager::class,
    //     ];
    // }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn() => auth()->user()->hasRole('manager'))
                ->action(function (WorkOrder $record) {
                    $record->load([
                        'workers',
                        'createdBy',
                        'priority',
                        'status',
                        'equipment',
                        'department',
                        'logUpdates' => fn($q) => $q->with('by'),
                        'logs' => fn($q) => $q->with('by')->orderByDesc('wol_dt'),
                        'reportSubmissions' => fn($q) => $q->with(['submittedBy', 'workers']),
                    ]);

                    $pdf = Pdf::loadView('reports.work-order-report', [
                        'workOrder' => $record,
                    ])
                        ->setPaper('a4', 'portrait')
                        ->setOption('isRemoteEnabled', false)
                        ->setOption('isHtml5ParserEnabled', true)
                        ->setOption('defaultFont', 'Helvetica')
                        ->setOption('margin_left', 12.7)
                        ->setOption('margin_right', 12.7)
                        ->setOption('margin_top', 12.7)
                        ->setOption('margin_bottom', 12.7);

                    return response()->streamDownload(
                        fn() => print($pdf->output()),
                        $record->wo_no . '.pdf',
                        ['Content-Type' => 'application/pdf'],
                    );
                }),
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
                    ->label('Reject Completion Request')
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
                                $status = Status::firstWhere('status_id', 'rca');
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

                                activity()
                                    ->performedOn($workOrder)
                                    ->useLog('WorkOrder')
                                    ->event($action->a_id)
                                    ->withProperties([
                                        'action_id' => $action->a_id,
                                        'status_id' => $status->status_id,
                                        'note' => $data['wol_note'] ?? ($data['wo_desc'] ?? null),
                                    ])
                                    ->log($action->a_past_tense);
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
                    ->label('Approve Completion Request')
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
                                            'insi_closed_dt' => $now,
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

                                activity()
                                    ->performedOn($workOrder)
                                    ->useLog('WorkOrder')
                                    ->event($action->a_id)
                                    ->withProperties([
                                        'action_id' => $action->a_id,
                                        'status_id' => $status->status_id,
                                        'note' => $data['wol_note'] ?? ($data['wo_desc'] ?? null),
                                    ])
                                    ->log($action->a_past_tense);
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

                                activity()
                                    ->performedOn($workOrder)
                                    ->useLog('WorkOrder')
                                    ->event($action->a_id)
                                    ->withProperties([
                                        'action_id' => $action->a_id,
                                        'status_id' => $status->status_id,
                                        'note' => $data['wol_note'] ?? ($data['wo_desc'] ?? null),
                                    ])
                                    ->log($action->a_past_tense);
                            });

                            $record->load(['workers', 'createdBy', 'priority']);
                            $canceller = auth()->user();
                            $note = $data['wol_note'];

                            // 1. Notify manager (canceller) — Confirmation
                            Mail::to($canceller->user_email)
                                ->queue(new WorkOrderManagerCancellationConfirmationMail(
                                    workOrder: $record,
                                    canceller: $canceller,
                                    reason: $note,
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
                Action::make('completeWorkOrder')
                    ->label('Complete Work Order')
                    ->visible(fn() => Auth::user()->can('completeWorkOrder', $this->record))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->modalHeading('Complete Work Order')
                    ->modalWidth(Width::Large)
                    ->closeModalByClickingAway(false)
                    ->modalCloseButton(false)
                    ->schema([
                        Textarea::make('wo_root_cause')
                            ->label('Root Cause')
                            ->required()
                            ->rows(4)
                            ->autosize(),
                        Textarea::make('wo_corrective_action')
                            ->label('Corrective Action')
                            ->required()
                            ->rows(4)
                            ->autosize(),
                        Textarea::make('wol_note')
                            ->label('Note')
                            ->required()
                            ->rows(4)
                            ->autosize(),
                    ])
                    ->action(function (array $data, WorkOrder $record) {
                        try {
                            DB::transaction(function () use ($data, $record) {
                                $now = now();
                                $workOrder = WorkOrder::where('wo_id', $record->wo_id)
                                    ->lockForUpdate()
                                    ->first();

                                if (! in_array($workOrder->wo_status_id, ['inprog'])) {
                                    throw new \Exception('Work order cannot be completed at its current status.');
                                }

                                $action = ModelsAction::find('cwo');
                                $status = Status::find('cmp');

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

                                $workOrder->update([
                                    'wo_root_cause' => $data['wo_root_cause'],
                                    'wo_corrective_action' => $data['wo_corrective_action'],
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
                                        'note' => $data['wol_note'] ?? ($data['wo_desc'] ?? null),
                                    ])
                                    ->log($action->a_past_tense);
                            });

                            $record->load(['workers', 'createdBy', 'priority']);
                            $completer = auth()->user();
                            $note = $data['wol_note'];

                            // 1. Notify manager (completer) — Confirmation
                            Mail::to($completer->user_email)
                                ->queue(new WorkOrderApprovalMail(
                                    workOrder: $record,
                                    approver: $completer,
                                    note: $note,
                                    recipientType: 'manager',
                                ));

                            // 2. Notify all assigned technicians — Update
                            foreach ($record->workers as $technician) {
                                if ($technician->user_email) {
                                    Mail::to($technician->user_email)
                                        ->queue(new WorkOrderApprovalMail(
                                            workOrder: $record,
                                            approver: $completer,
                                            note: $note,
                                            recipientType: 'technician',
                                        ));
                                }
                            }

                            Notification::make()
                                ->title('Work order completed.')
                                ->success()
                                ->send();

                            $this->redirect(WorkOrderResource::getUrl('view', ['record' => $record->wo_id]), navigate: true);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to complete work order.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('assignWorkOrder')
                    ->label('Assign Work Order')
                    ->visible(fn() => Auth::user()->can('assignWorkOrder', $this->record))
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->modalHeading('Assign Work Order')
                    ->modalWidth('lg')
                    ->closeModalByClickingAway(false)
                    ->modalCloseButton(false)
                    ->schema([
                        Select::make('worker_ids')
                            ->label('Assign Technicians')
                            ->options(function () {
                                $depId = auth()->user()->user_dep_id;
                                if (! $depId) {
                                    return [];
                                }

                                return AppUser::whereHas('roles', fn($q) => $q->where('name', 'technician'))
                                    ->where('user_dep_id', $depId)
                                    ->get()
                                    ->mapWithKeys(fn($user) => [
                                        $user->user_id => "{$user->user_fname} {$user->user_lname}",
                                    ]);
                            })
                            ->multiple()
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Textarea::make('wo_desc')
                            ->label('Manager Description')
                            ->rows(4),
                        Textarea::make('wo_note')
                            ->label('Note')
                            ->rows(4),
                    ])
                    ->action(function (array $data, WorkOrder $record) {
                        $assignAction = ModelsAction::find('as');
                        $inprogStatus = Status::find('inprog');

                        try {
                            DB::transaction(function () use ($data, $record, $assignAction, $inprogStatus) {
                                $now = now();

                                $workOrder = WorkOrder::where('wo_id', $record->wo_id)
                                    ->lockForUpdate()
                                    ->first();

                                if ($workOrder->wo_status_id !== 'pndwor') {
                                    throw new \Exception('Work order status is not pending.');
                                }

                                WorkOrderLog::create([
                                    'wol_wo_id' => $workOrder->wo_id,
                                    'wol_a_id' => $assignAction->a_id,
                                    'wol_status_id' => $inprogStatus->status_id,
                                    'wol_a_log' => $assignAction->a_past_tense,
                                    'wol_status_log' => $inprogStatus->status_title,
                                    'wol_note' => $data['wo_note'],
                                    'wol_by' => auth()->id(),
                                    'wol_dt' => $now,
                                ]);

                                $workOrder->update([
                                    'wo_status_id' => $inprogStatus->status_id,
                                    'wo_desc' => $data['wo_desc'],
                                ]);

                                // Sync technicians
                                $workerIds = $data['worker_ids'] ?? [];
                                $workOrder->workers()->sync($workerIds);

                                activity()
                                    ->performedOn($workOrder)
                                    ->useLog('WorkOrder')
                                    ->event($assignAction->a_id)
                                    ->withProperties([
                                        'action_id' => $assignAction->a_id,
                                        'status_id' => $inprogStatus->status_id,
                                        'worker_ids' => $workerIds,
                                        'note' => $data['wo_desc'],
                                    ])
                                    ->log($assignAction->a_past_tense);
                            });

                            $record->load(['workers', 'createdBy', 'priority']);
                            $manager = auth()->user();

                            // 1. Notify Manager (Confirmation)
                            Mail::to($manager->user_email)
                                ->queue(new WorkOrderApprovedMail($record, $manager, 'manager'));

                            // 2. Notify Requestor (Update)
                            if ($record->createdBy && $record->createdBy->user_email) {
                                Mail::to($record->createdBy->user_email)
                                    ->queue(new WorkOrderApprovedMail($record, $record->createdBy, 'requestor'));
                            }

                            // 3. Notify Technicians (Action Required)
                            $emails = $record->workers
                                ->pluck('user_email')
                                ->filter()
                                ->unique()
                                ->values();

                            if ($emails->count() > 0) {
                                Mail::to($emails->all())
                                    ->queue(new WorkOrderAssignedMail($record, null));
                            }

                            Notification::make()
                                ->title('Work order ' . strtolower($assignAction->a_past_tense) . ' successfully.')
                                ->success()
                                ->send();

                            // $this->redirect(WorkOrderResource::getUrl('view', ['record' => $record->wo_id]), navigate: true);
                            $this->redirect(WorkOrderResource::getUrl('view', ['record' => $record]), navigate: true);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to ' . strtolower($assignAction->a_past_tense) . ' work order.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('rejectWorkOrder')
                    ->label('Reject Work Order')
                    ->visible(fn() => Auth::user()->can('rejectWorkOrder', $this->record))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Reject Work Order')
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

                                if ($workOrder->wo_status_id !== 'pndwor') {
                                    throw new \Exception('Work order is not pending approval.');
                                }

                                $action = ModelsAction::find('reject');
                                $status = Status::find('rej');

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
                                        'note' => $data['wol_note'] ?? ($data['wo_desc'] ?? null),
                                    ])
                                    ->log($action->a_past_tense);
                            });

                            $record->load(['createdBy', 'priority']);
                            $manager = auth()->user();
                            $reason = $data['wol_note'];

                            // 1. Notify Manager (Confirmation)
                            Mail::to($manager->user_email)
                                ->queue(new WorkOrderRejectedMail($record, $manager, $reason, 'manager'));

                            // 2. Notify Requestor (Update)
                            if ($record->createdBy && $record->createdBy->user_email) {
                                Mail::to($record->createdBy->user_email)
                                    ->queue(new WorkOrderRejectedMail($record, $record->createdBy, $reason, 'requestor'));
                            }

                            Notification::make()
                                ->title('Work order rejected.')
                                ->success()
                                ->send();

                            $this->redirect(WorkOrderResource::getUrl('view', ['record' => $record->wo_id]), navigate: true);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to reject work order.')
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

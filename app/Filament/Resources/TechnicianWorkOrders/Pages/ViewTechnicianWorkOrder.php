<?php

namespace App\Filament\Resources\TechnicianWorkOrders\Pages;

use App\Filament\Resources\TechnicianWorkOrders\RelationManagers\LogsRelationManager;
use App\Filament\Resources\TechnicianWorkOrders\RelationManagers\LogUpdatesRelationManager;
use App\Filament\Resources\TechnicianWorkOrders\RelationManagers\ReportSubmissionsRelationManager;
use App\Filament\Resources\TechnicianWorkOrders\TechnicianWorkOrderResource;
use App\Mail\WorkOrderCompletionRequestedMail;
use App\Models\Action as ModelsAction;
use App\Models\AppUser;
use App\Models\ReportSubmission;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use App\Models\WorkOrderLogUpdate;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ViewTechnicianWorkOrder extends ViewRecord
{
    protected static string $resource = TechnicianWorkOrderResource::class;

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
            ReportSubmissionsRelationManager::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
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
                    try {
                        DB::transaction(function () use ($data, $record) {
                            $workOrder = WorkOrder::where('wo_id', $record->wo_id)
                                ->lockForUpdate()
                                ->first();

                            if ($workOrder->wo_status_id !== 'inprog') {
                                throw new \Exception('Work order status is not In-Progress.');
                            }
                            WorkOrderLogUpdate::create([
                                'wolu_wo_id'      => $record->wo_id,
                                'wolu_update_note' => $data['wolu_update_note'],
                                'wolu_attachments' => $data['wolu_attachments'] ?? null,
                                'wolu_by'         => auth()->id(),
                                'wolu_dt'         => now(),
                            ]);

                            Notification::make()
                                ->title('Update added successfully.')
                                ->success()
                                ->send();

                            $this->js('Livewire.dispatch("refreshLogsRelationManager")');
                        });
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to add update.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('addReport')
                ->label('Add Report')
                ->visible(fn() => Auth::user()->can('addReport', $this->record))
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->modalHeading('Add Work Order Report')
                ->modalWidth(Width::Large)
                ->closeModalByClickingAway(false)
                ->modalCloseButton(false)
                ->schema([
                    DatePicker::make('rs_work_date')
                        ->label('Work Date')
                        ->required()
                        ->live(onBlur: false)
                        ->default(today())
                        ->minDate(fn() => $this->record->wo_created_dt)
                        ->maxDate(today())
                        ->hint(function (Get $get) {
                            if (!$get('rs_work_date')) return null;

                            $exists = ReportSubmission::where('rs_wo_id', $this->record->wo_id)
                                ->whereDate('rs_work_date', $get('rs_work_date'))
                                ->exists();

                            return $exists ? 'A report already exists for this date.' : null;
                        })
                        ->hintColor('warning'),
                    Select::make('rs_worker_ids')
                        ->label('Workers')
                        ->options(function (Get $get) {
                            return AppUser::whereHas('roles', fn($q) => $q->where('name', 'technician'))
                                ->where('user_dep_id', auth()->user()->user_dep_id)
                                ->get()
                                ->mapWithKeys(fn($user) => [
                                    $user->user_id => "{$user->user_fname} {$user->user_lname}",
                                ]);
                        })
                        ->multiple()
                        ->required()
                        ->searchable()
                        ->preload()
                        ->native(false),
                ])
                ->action(function (array $data, WorkOrder $record) {
                    try {
                        DB::transaction(function () use ($data, $record) {
                            $workOrder = WorkOrder::where('wo_id', $record->wo_id)
                                ->lockForUpdate()
                                ->first();

                            if ($workOrder->wo_status_id !== 'inprog') {
                                throw new \Exception('Work order status is not In-Progress.');
                            }

                            $report = ReportSubmission::create([
                                'rs_wo_id'        => $record->wo_id,
                                'rs_work_date'    => $data['rs_work_date'],
                                'rs_submitted_by' => auth()->id(),
                                'rs_submitted_dt' => now(),
                            ]);

                            $report->workers()->sync($data['rs_worker_ids']);
                        });

                        Notification::make()
                            ->title('Report submitted successfully.')
                            ->success()
                            ->send();

                        $this->js('Livewire.dispatch("refreshLogsRelationManager")');
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to submit report.')
                            ->body('Please try again or contact support.' . ' ' . $e)
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('requestCompletion')
                ->label('Request Completion')
                ->visible(fn() => Auth::user()->can('requestCompletion', $this->record))
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->modalHeading('Request Completion')
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
                            $workOrder = WorkOrder::where('wo_id', $record->wo_id)
                                ->lockForUpdate()
                                ->first();

                            if ($workOrder->wo_status_id !== 'inprog') {
                                throw new \Exception('Work order status is not In-Progress.');
                            }

                            $action = ModelsAction::find('reqcom');
                            $status = Status::find('pca');

                            WorkOrderLog::create([
                                'wol_wo_id'      => $workOrder->wo_id,
                                'wol_a_id'       => $action->a_id,
                                'wol_status_id'  => $status->status_id,
                                'wol_a_log'      => $action->a_past_tense,
                                'wol_status_log' => $status->status_title,
                                'wol_note'       => $data['wol_note'] ?? null,
                                'wol_by'         => auth()->id(),
                                'wol_dt'         => now(),
                            ]);

                            $workOrder->update([
                                'wo_status_id' => $status->status_id,
                            ]);
                        });

                        // Load relationships needed for emails
                        $record->load(['workers', 'createdBy', 'priority']);
                        $requestor = auth()->user();
                        $note = $data['wol_note'] ?? null;
                        $woUrl = config('app.url') . '/technician-work-orders/' . $record->wo_id; // adjust panel path as needed

                        $sharedData = [
                            'workOrder' => $record,
                            'requestor' => $requestor,
                            'note'      => $note,
                            'ctaUrl'    => $woUrl,
                        ];

                        // 1. Notify requestor — Confirmation
                        Mail::to($requestor->user_email)
                            ->queue((new WorkOrderCompletionRequestedMail($record, $requestor, 'requestor', $note))
                                ->with(array_merge($sharedData, [
                                    'headerColor'    => '#16a34a',
                                    'headerSubColor' => '#bbf7d0',
                                    'headerIcon'     => '✅',
                                    'headerTitle'    => 'Completion Request Submitted',
                                    'headerSubtitle' => 'Your completion request has been submitted and is awaiting approval.',
                                    'bodyMessage'    => 'Your request to complete the following work order has been successfully submitted. You will be notified once it has been reviewed.',
                                    'ctaLabel'       => 'View Work Order',
                                ])));

                        // 2. Notify managers of the same department — Action Required
                        $managers = AppUser::whereHas('roles', fn($q) => $q->where('name', 'manager'))
                            ->where('user_dep_id', $requestor->user_dep_id)
                            ->get();

                        foreach ($managers as $manager) {
                            if ($manager->user_email) {
                                Mail::to($manager->user_email)
                                    ->queue((new WorkOrderCompletionRequestedMail($record, $requestor, 'manager', $note))
                                        ->with(array_merge($sharedData, [
                                            'headerColor'    => '#d97706',
                                            'headerSubColor' => '#fde68a',
                                            'headerIcon'     => '⚠️',
                                            'headerTitle'    => 'Action Required: Completion Approval',
                                            'headerSubtitle' => 'A technician has requested completion approval for a work order.',
                                            'bodyMessage'    => 'The following work order has been submitted for completion approval. Please review and take the appropriate action.',
                                            'ctaLabel'       => 'Review Work Order',
                                        ])));
                            }
                        }

                        // 3. Notify groupmate technicians — Update
                        $groupmates = $record->workers->where('user_id', '!=', $requestor->user_id);
                        foreach ($groupmates as $technician) {
                            if ($technician->user_email) {
                                Mail::to($technician->user_email)
                                    ->queue((new WorkOrderCompletionRequestedMail($record, $requestor, 'technician', $note))
                                        ->with(array_merge($sharedData, [
                                            'headerColor'    => '#2563eb',
                                            'headerSubColor' => '#bfdbfe',
                                            'headerIcon'     => '📋',
                                            'headerTitle'    => 'Work Order Update',
                                            'headerSubtitle' => 'A completion request has been submitted for your work order.',
                                            'bodyMessage'    => 'Your teammate has submitted a completion request for the following work order. No action is required from you at this time.',
                                            'ctaLabel'       => 'View Work Order',
                                        ])));
                            }
                        }

                        Notification::make()
                            ->title('Completion requested successfully.')
                            ->success()
                            ->send();

                        $this->redirect(TechnicianWorkOrderResource::getUrl('view', ['record' => $record->wo_id]), navigate: true);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to request completion.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

<?php

namespace App\Filament\Resources\InspectionItems\Pages;

use App\Filament\Resources\InspectionItems\InspectionItemResource;
use App\Mail\InspectionItemDisregardedMail;
use App\Mail\WorkOrderAssignedMail;
use App\Mail\WorkOrderConfirmationMail;
use App\Models\Action as ModelsAction;
use App\Models\AppUser;
use App\Models\InspectionItem;
use App\Models\InspectionItemLog;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ViewInspectionItem extends ViewRecord
{
    protected static string $resource = InspectionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
            Action::make('disregard')
                ->label('Disregard Finding')
                ->icon('heroicon-o-x-mark')
                ->visible(fn() => Auth::user()->can('disregard', $this->record))
                ->color('gray')
                ->modalHeading('Disregard Inspection Finding')
                ->modalWidth(Width::Large)
                ->closeModalByClickingAway(false)
                ->modalCloseButton(false)
                ->schema([
                    Textarea::make('inil_remarks')
                        ->label('Reason for Disregarding')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    try {
                        DB::transaction(function () use ($data) {
                            $now = now();

                            $inspectionItem = InspectionItem::where('insi_id', '=', $this->record->insi_id, 'and')
                                ->lockForUpdate()
                                ->first();

                            if ($inspectionItem->insi_status_id !== 'pnd') {
                                throw new \Exception('This inspection item is no longer pending.');
                            }

                            $action = ModelsAction::query()->where('a_id', '=', 'drg')->firstOrFail();
                            $status = Status::query()->where('status_id', '=', 'drg')->firstOrFail();

                            $inspectionItem->update([
                                'insi_status_id' => 'drg',
                                'insi_closed_dt' => $now,
                            ]);

                            InspectionItemLog::create([
                                'inil_insi_id' => $inspectionItem->insi_id,
                                'inil_a_id' => $action->a_id,
                                'inil_status_id' => $status->status_id,
                                'inil_action_made' => $action->a_past_tense,
                                'inil_status_log' => $status->status_title,
                                'inil_remarks' => $data['inil_remarks'],
                                'inil_by' => Auth::id(),
                                'inil_dt' => $now,
                            ]);
                        });

                        // Notify manager — Confirmation
                        $inspection = $this->record->inspection;
                        $inspection->load(['equipment', 'conductedBy', 'department']);

                        $managers = AppUser::whereHas('roles', fn($q) => $q->where('name', 'manager'))
                            ->where('user_dep_id', $inspection->ins_dep_id)
                            ->get();

                        foreach ($managers as $manager) {
                            if ($manager->user_email) {
                                Mail::to($manager->user_email)
                                    ->queue(new InspectionItemDisregardedMail(
                                        inspection: $inspection,
                                        inspectionItem: $this->record,
                                        manager: $manager,
                                        reason: $data['inil_remarks'],
                                    ));
                            }
                        }

                        Notification::make()
                            ->title('Finding disregarded.')
                            ->success()
                            ->send();

                        $this->redirect(InspectionItemResource::getUrl('view', ['record' => $this->record->insi_id]), navigate: true);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to disregard finding.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('makeWorkOrder')
                ->model(WorkOrder::class)
                ->label('Create Work Order')
                ->visible(fn() => Auth::user()->can('makeWorkOrder', $this->record))
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->modalHeading('Create Work Order from Inspection Finding')
                ->modalWidth(Width::SevenExtraLarge)
                ->closeModalByClickingAway(false)
                ->modalCloseButton(false)
                ->schema([
                    Wizard::make([
                        Step::make('Work Order Details')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Section::make('Equipment')
                                    ->icon('heroicon-o-truck')
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->schema([
                                        Select::make('wo_eqm_id')
                                            ->label('Equipment')
                                            ->relationship('equipment', 'eqm_name')
                                            // ->default(fn($record) => $record?->inspection?->ins_eqm_id)
                                            ->default(fn() => $this->record?->inspection?->ins_eqm_id)
                                            ->searchable()
                                            ->disabled()
                                            ->dehydrated()
                                            ->preload()
                                            ->required()
                                            ->native(false)
                                            ->live(),
                                        Select::make('wo_dep_id')
                                            ->label('Department')
                                            ->relationship(
                                                'department',
                                                'dep_name',
                                                fn(Builder $query) => $query->where('is_maintenance', 1)
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->native(false)
                                            ->live()
                                            ->visible(fn() => Auth::user()->hasRole('super_admin')),
                                    ]),

                                Section::make('Details')
                                    ->icon('heroicon-o-document-text')
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->schema([
                                        Textarea::make('wo_title')
                                            ->label('Title')
                                            ->required(),
                                        Select::make('wo_prio_id')
                                            ->label('Priority')
                                            ->relationship('priority', 'prio_name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->native(false),
                                        Textarea::make('wo_desc')
                                            ->label('Description')
                                            ->required()
                                            ->columnSpanFull(),
                                        FileUpload::make('wo_attachments')
                                            ->label('Attachments')
                                            ->multiple()
                                            ->nullable()
                                            ->columnSpanFull()
                                            ->maxFiles(10)
                                            ->maxParallelUploads(5)
                                            ->panelLayout('grid')
                                            ->reorderable()
                                            ->appendFiles()
                                            ->openable()
                                            ->downloadable()
                                            ->previewable()
                                            ->maxSize(10240)
                                            ->imageEditor(),
                                    ]),
                            ]),

                        Step::make('Assign Workers')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Select::make('worker_ids')
                                    ->label('Assigned Workers')
                                    ->options(function (Get $get) {
                                        $depId = Auth::user()->hasRole('super_admin')
                                            ? $get('wo_dep_id')
                                            : Auth::user()->user_dep_id;

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
                                    ->rules(['array', 'exists:app_users,user_id'])
                                    ->multiple()
                                    ->required()
                                    ->native(false)
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    try {
                        DB::transaction(function () use ($data) {
                            $now = now();

                            if (! Auth::user()->hasRole('super_admin')) {
                                $data['wo_dep_id'] = Auth::user()->user_dep_id;
                            }

                            $depCode = DB::table('departments')
                                ->where('dep_id', $data['wo_dep_id'])
                                ->value('dep_code');

                            $count = DB::table('work_orders')
                                ->where('wo_dep_id', $data['wo_dep_id'])
                                ->whereDate('wo_created_dt', $now->toDateString())
                                ->count() + 1;

                            $inspectionItem = InspectionItem::where('insi_id', '=', $this->record->insi_id, 'and')
                                ->lockForUpdate()
                                ->first();

                            if ($inspectionItem->insi_status_id !== 'pnd') {
                                throw new \Exception('This inspection item is no longer pending.');
                            }

                            $woAction = ModelsAction::query()->where('a_id', '=', 'create')->firstOrFail();
                            $woStatus = Status::query()->where('status_id', '=', 'pnd')->firstOrFail();
                            $woStatusInProg = Status::query()->where('status_id', '=', 'inprog')->firstOrFail();
                            $insiAction = ModelsAction::query()->where('a_id', '=', 'mwo')->firstOrFail();
                            $insiStatus = Status::query()->where('status_id', '=', 'inprog')->firstOrFail();

                            $workerIds = $data['worker_ids'] ?? [];
                            unset($data['worker_ids']);

                            $selectedWorkers = AppUser::findMany($workerIds);

                            if ($selectedWorkers->count() !== count($workerIds)) {
                                throw new \Exception('One or more assigned workers do not exist.');
                            }

                            foreach ($selectedWorkers as $worker) {
                                if ($worker->user_dep_id !== $data['wo_dep_id'] || ! $worker->hasRole('technician')) {
                                    throw new \Exception('One or more assigned workers are invalid for the selected department.');
                                }
                            }

                            $workerIds = $selectedWorkers->pluck('user_id')->toArray();

                            $workOrder = WorkOrder::create([
                                ...$data,
                                'wo_no' => 'WO-' . $depCode . '-' . $now->format('ymd') . str_pad($count, 3, '0', STR_PAD_LEFT),
                                'wo_eqm_id' => $this->record?->inspection?->ins_eqm_id,
                                'wo_insi_id' => $inspectionItem->insi_id,
                                'wo_dep_id' => Auth::user()->hasRole('super_admin') ? $data['wo_dep_id'] : Auth::user()->user_dep_id,
                                'wo_status_id' => 'inprog',
                                'wo_created_by' => Auth::id(),
                                'wo_created_dt' => $now,
                            ]);

                            $workOrder->workers()->sync($workerIds);

                            $workOrder->load(['workers', 'priority', 'createdBy']);

                            WorkOrderLog::create([
                                'wol_wo_id' => $workOrder->wo_id,
                                'wol_a_id' => $woAction->a_id,
                                'wol_status_id' => $woStatusInProg->status_id,
                                'wol_a_log' => $woAction->a_past_tense,
                                'wol_status_log' => $woStatusInProg->status_title,
                                'wol_by' => Auth::id(),
                                'wol_dt' => $now,
                            ]);

                            $inspectionItem->update(['insi_status_id' => 'inprog']);

                            InspectionItemLog::create([
                                'inil_insi_id' => $inspectionItem->insi_id,
                                'inil_a_id' => $insiAction->a_id,
                                'inil_status_id' => $insiStatus->status_id,
                                'inil_action_made' => $insiAction->a_past_tense,
                                'inil_status_log' => $insiStatus->status_title,
                                'inil_remarks' => null,
                                'inil_wo_id' => $insiAction->a_id === 'mwo' ? $workOrder->wo_id : null,
                                'inil_by' => Auth::id(),
                                'inil_dt' => $now,
                            ]);

                            // Notify manager
                            $manager = AppUser::whereHas('roles', fn($q) => $q->where('name', 'manager'))
                                ->where('user_dep_id', $workOrder->wo_dep_id)
                                ->first();

                            if ($manager?->user_email) {
                                Mail::to($manager->user_email)
                                    ->queue(new WorkOrderConfirmationMail($workOrder));
                            }

                            // Notify each assigned technician
                            foreach ($workOrder->workers as $worker) {
                                if ($worker->user_email) {
                                    Mail::to($worker->user_email)
                                        ->queue(new WorkOrderAssignedMail($workOrder, $worker));
                                }
                            }
                        });

                        Notification::make()
                            ->title('Work order created successfully.')
                            ->success()
                            ->send();

                        $this->redirect(InspectionItemResource::getUrl('view', ['record' => $this->record->insi_id]), navigate: true);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to create work order.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

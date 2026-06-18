<?php

namespace App\Filament\Resources\MaintenanceTasks\Pages;

use App\Filament\Resources\MaintenanceTasks\MaintenanceTaskResource;
use App\Mail\WorkOrderAssignedMail;
use App\Mail\WorkOrderConfirmationMail;
use App\Models\Action as ModelsAction;
use App\Models\AppUser;
use App\Models\EquipmentTasksSchedule;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceTaskLog;
use App\Models\Priority;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ViewMaintenanceTask extends ViewRecord
{
    protected static string $resource = MaintenanceTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(MaintenanceTaskResource::getUrl('index')),

            ActionGroup::make([
                Action::make('makeWorkOrder')
                    ->label('New Work Order')
                    ->color('primary')
                    ->modalWidth('lg')
                    ->closeModalByClickingAway(false)
                    ->modalHeading('New Work Order')
                    ->modalCloseButton(false)
                    ->modalWidth(Width::SevenExtraLarge)
                    ->icon(fn () => DB::table('actions')->where('a_id', 'mwo')->value('a_icon'))
                    ->visible(fn () => Auth::user()->can('makeWorkOrder', $this->record))
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
                                                ->relationship('equipmentUnit', 'eqm_name')
                                                ->default(fn () => $this->record->mt_eqm_id)
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
                                                    fn (Builder $query) => $query->where('is_maintenance', 1)
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->native(false)
                                                ->live()
                                                ->visible(fn () => Auth::user()->hasRole('super_admin')),
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
                                                ->options(Priority::pluck('prio_name', 'prio_id'))
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

                                            return AppUser::whereHas('roles', fn ($q) => $q->where('name', 'technician'))
                                                ->where('user_dep_id', $depId)
                                                ->get()
                                                ->mapWithKeys(fn ($user) => [
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

                                $maintenanceTask = MaintenanceTask::where('mt_id', $this->record->mt_id)
                                    ->lockForUpdate()
                                    ->first();

                                if ($maintenanceTask->mt_status_id !== 'pnd') {
                                    throw new \Exception('This maintenance task is no longer pending.');
                                }

                                $woAction = ModelsAction::where('a_id', 'create')->firstOrFail();
                                $woStatusInProg = Status::where('status_id', 'inprog')->firstOrFail();
                                $mwoAction = ModelsAction::query()->where('a_id', '=', 'mwo')->firstOrFail();

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
                                    'wo_no' => 'WO-'.$depCode.'-'.$now->format('ymd').str_pad($count, 3, '0', STR_PAD_LEFT),
                                    'wo_eqm_id' => $maintenanceTask->mt_eqm_id,
                                    'wo_mt_id' => $maintenanceTask->mt_id, // 👈 prefilled from maintenance task
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

                                $maintenanceTask->update([
                                    'mt_status_id' => 'inprog',
                                ]);

                                MaintenanceTaskLog::create([
                                    'mtl_mt_id' => $maintenanceTask->mt_id,
                                    'mtl_status_id' => 'inprog',
                                    'mtl_due_dt' => $maintenanceTask->mt_due_dt,
                                    'mtl_last_act_made' => $mwoAction->a_id,
                                    'mtl_wo_id' => $workOrder->wo_id,
                                    'mtl_by' => Auth::id(),
                                    'mtl_dt' => $now,
                                ]);

                                // Notify manager
                                $managers = AppUser::whereHas('roles', fn ($q) => $q->where('name', 'manager'))
                                    ->where('user_dep_id', $workOrder->wo_dep_id)
                                    ->get();

                                foreach ($managers as $manager) {
                                    if ($manager->user_email) {
                                        Mail::to($manager->user_email)
                                            ->queue(new WorkOrderConfirmationMail($workOrder));
                                    }
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

                            $this->redirect(MaintenanceTaskResource::getUrl('view', ['record' => $this->record->mt_id]), navigate: true);
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Failed to create work order.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('snooze')
                    ->label('Snooze')
                    ->color('info')
                    ->modalWidth('lg')
                    ->closeModalByClickingAway(false)
                    ->modalHeading('Snooze Task')
                    ->modalCloseButton(false)
                    ->icon(fn () => DB::table('actions')->where('a_id', 'snz')->value('a_icon'))
                    ->visible(fn () => Auth::user()->can('snooze', $this->record))
                    ->schema([
                        DateTimePicker::make('mtl_due_dt')
                            ->label('Extend Due Date')
                            ->displayFormat('M d, Y | h:i A')
                            ->seconds(false)
                            ->required()
                            // ->minDate(fn(MaintenanceTask $record) => Carbon::parse($record->mt_due_dt)->addDay())
                            ->rules([
                                fn (MaintenanceTask $record) => function (string $attribute, $value, $fail) use ($record) {
                                    $newDate = Carbon::parse($value);

                                    if ($newDate->lte(now())) {
                                        $fail('New due date must be in the future.');
                                    }

                                    if ($newDate->lte(Carbon::parse($record->mt_due_dt))) {
                                        $fail('New due date must be later than the current due date.');
                                    }
                                },
                            ]),
                        Textarea::make('mtl_remarks')
                            ->label('Remarks')
                            ->required(),
                    ])
                    ->action(function (array $data, MaintenanceTask $record, Action $action) {
                        if (! in_array($record->mt_status_id, ['pnd'])) {
                            Notification::make()
                                ->title('Invalid Status')
                                ->body('Task must be Pending to be Snoozed.')
                                ->danger()
                                ->send();

                            $action->halt();

                            return;
                        }

                        try {
                            DB::transaction(function () use ($data, $record) {
                                $now = Carbon::now();

                                $record->update([
                                    'mt_status_id' => 'snz',
                                    'mt_due_dt' => $data['mtl_due_dt'],
                                ]);

                                MaintenanceTaskLog::create([
                                    'mtl_mt_id' => $record->mt_id,
                                    'mtl_status_id' => 'snz',
                                    'mtl_due_dt' => $data['mtl_due_dt'],
                                    'mtl_last_act_made' => 'snz',
                                    'mtl_remarks' => $data['mtl_remarks'],
                                    'mtl_by' => auth()->id(),
                                    'mtl_dt' => $now,
                                ]);

                                $schedule = EquipmentTasksSchedule::where('ets_eqm_id', $record->mt_eqm_id)
                                    ->where('ets_dep_id', $record->mt_dep_id)
                                    ->where('ets_task_id', $record->mt_task_id)
                                    ->first();

                                if ($schedule) {
                                    $schedule->update([
                                        'ets_due_dt' => $data['mtl_due_dt'],
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('Task Snoozed')
                                ->success()
                                ->send();

                            $this->js('Livewire.dispatch("refreshRelationManager")');
                        } catch (Throwable $th) {
                            Notification::make()
                                ->title('Action Failed')
                                ->body('An error occurred while updating the task. No changes were saved.'.''.$th->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('markAsComplete')
                    ->label('Mark as Complete')
                    ->color('success')
                    ->modalWidth('lg')
                    ->closeModalByClickingAway(false)
                    ->modalHeading('Complete Task')
                    ->modalCloseButton(false)
                    ->icon(fn () => DB::table('actions')->where('a_id', 'mac')->value('a_icon'))
                    ->visible(fn () => Auth::user()->can('markAsComplete', $this->record))
                    ->schema([
                        Textarea::make('mtl_remarks')
                            ->label('Remarks')
                            ->required(),
                    ])
                    ->action(function (array $data, MaintenanceTask $record) {
                        if (! in_array($record->mt_status_id, ['snz', 'pnd'])) {
                            Notification::make()
                                ->title('Invalid Status')
                                ->body('Task must be Pending or Snoozed to be marked complete.')
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            DB::transaction(function () use ($data, $record) {
                                $now = Carbon::now();

                                $record->update([
                                    'mt_status_id' => 'cmp',
                                    'mt_closed_dt' => $now,
                                ]);

                                MaintenanceTaskLog::create([
                                    'mtl_mt_id' => $record->mt_id,
                                    'mtl_status_id' => 'cmp',
                                    'mtl_last_act_made' => 'mac',
                                    'mtl_remarks' => $data['mtl_remarks'],
                                    'mtl_by' => auth()->id(),
                                    'mtl_dt' => $now,
                                ]);

                                $schedule = EquipmentTasksSchedule::where('ets_eqm_id', $record->mt_eqm_id)
                                    ->where('ets_dep_id', $record->mt_dep_id)
                                    ->where('ets_task_id', $record->mt_task_id)
                                    ->first();

                                if ($schedule) {
                                    $schedule->update([
                                        'ets_due_dt' => Carbon::parse($now)
                                            ->addYears($schedule->ets_itrv_years ?? 0)
                                            ->addMonths($schedule->ets_itrv_months ?? 0)
                                            ->addWeeks($schedule->ets_itrv_weeks ?? 0)
                                            ->addDays($schedule->ets_itrv_days ?? 0)
                                            ->setTimeFromTimeString($schedule->ets_sched_time ?? '00:00:00'),
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('Task marked as complete')
                                ->success()
                                ->send();

                            $this->js('Livewire.dispatch("refreshRelationManager")');
                        } catch (Throwable $th) {
                            Notification::make()
                                ->title('Action Failed')
                                ->body('An error occurred while updating the task. No changes were saved.'.''.$th->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                // EditAction::make(),
            ]),
        ];
    }
}

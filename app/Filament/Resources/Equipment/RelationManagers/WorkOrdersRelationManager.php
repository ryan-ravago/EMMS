<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Mail\WorkOrderAssignedMail;
use App\Mail\WorkOrderConfirmationMail;
use App\Models\Action as ModelsAction;
use App\Models\AppUser;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\Priority;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WorkOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'workOrders';

    // protected static ?string $relatedResource = WorkOrderResource::class;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $query = $ownerRecord->workOrders();
        if (! Auth::user()->hasRole('super_admin')) {
            $query->where('wo_dep_id', Auth::user()->user_dep_id);
        }

        return (string) $query->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->authorize(true)
                    ->label('Make Work Order')
                    ->color('primary')
                    ->closeModalByClickingAway(false)
                    ->modalHeading('Make Work Order')
                    ->modalCloseButton(false)
                    ->modalWidth(Width::SevenExtraLarge)
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
                                                ->options(
                                                    Equipment::where('eqm_id', $this->getOwnerRecord()->getKey())
                                                        ->pluck('eqm_name', 'eqm_id')
                                                )
                                                ->default(fn () => $this->getOwnerRecord()->getKey())
                                                ->disabled()
                                                ->dehydrated()
                                                ->required()
                                                ->native(false)
                                                ->live(),
                                            Select::make('wo_dep_id')
                                                ->label('Department')
                                                ->options(
                                                    Department::where('is_maintenance', 1)
                                                        ->pluck('dep_name', 'dep_id')
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

                                $woAction = ModelsAction::where('a_id', 'create')->firstOrFail();
                                $woStatusInProg = Status::where('status_id', 'inprog')->firstOrFail();
                                $mwoAction = ModelsAction::where('a_id', 'mwo')->firstOrFail();

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
                                    'wo_eqm_id' => $this->getOwnerRecord()->getKey(),
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

                                // Notify managers
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
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to create work order.')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->columns([
                TextColumn::make('wo_no')
                    ->label('WO No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->sortable()
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
                TextColumn::make('wo_title')
                    ->label('Title')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('priority.prio_name')
                    ->label('Priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn (WorkOrder $record) => $record->status->status_color)
                    ->icon(fn (WorkOrder $record) => $record->status->status_icon)
                    ->sortable(),
                TextColumn::make('workers')
                    ->badge()
                    ->listWithLineBreaks()
                    ->icon('heroicon-o-user-circle')
                    ->state(fn ($record) => $record->workers->map(fn ($w) => "{$w->user_fname} {$w->user_lname}")->toArray()),
                TextColumn::make('wo_closed_dt')
                    ->label('Closed At')
                    ->dateTime('M d, Y h:i A')
                    ->color('gray')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('wo_created_dt')
                    ->label('Created At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (! Auth::user()->hasRole('super_admin')) {
                    $query->where('wo_dep_id', Auth::user()->user_dep_id);
                }
            })
            ->filters([
                SelectFilter::make('wo_dep_id')
                    ->label('Department')
                    ->relationship('department', 'dep_name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
                SelectFilter::make('wo_prio_id')
                    ->label('Priority')
                    ->relationship('priority', 'prio_name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('wo_status_id')
                    ->label('Status')
                    ->relationship('status', 'status_title')
                    ->searchable()
                    ->preload(),
                Filter::make('wo_created_dt')
                    ->label('Created At')
                    ->schema([
                        DatePicker::make('from')->label('From')->native(false),
                        DatePicker::make('until')->label('Until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('wo_created_dt', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('wo_created_dt', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Model $record): string => WorkOrderResource::getUrl('view', ['record' => $record])),
                EditAction::make(),
            ]);
    }
}

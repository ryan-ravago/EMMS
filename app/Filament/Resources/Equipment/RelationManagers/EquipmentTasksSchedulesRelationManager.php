<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use Filament\Actions\Action;
use App\Models\Task;
use App\Models\TaskUsageType;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

class EquipmentTasksSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'equipmentTasksSchedules';

    protected static ?string $title = 'Tasks for Preventive';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['ets_eqm_id'] = $this->getOwnerRecord()->getKey();
        $data['ets_dep_id'] = auth()->user()->user_dep_id;

        return $data;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->department?->dep_code === 'PREV';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Schedule Details')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(4)
                    ->schema([
                        Select::make('ets_task_id')
                            ->label('Task')
                            ->relationship(
                                name: 'task',
                                titleAttribute: 'task_name',
                                modifyQueryUsing: fn($query) => $query
                                    ->where('task_tut_id', 2)
                                    ->where('task_dep_id', auth()->user()->user_dep_id)
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpan(3)
                            ->required()
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('task_name')
                                    ->label('Task Name')
                                    ->placeholder('Enter task name')
                                    ->required()
                                    ->columnSpanFull()
                                    ->unique(
                                        table: Task::class,
                                        column: 'task_name',
                                        modifyRuleUsing: fn(Unique $rule) => $rule->where('task_dep_id', auth()->user()->user_dep_id),
                                        ignoreRecord: true,
                                    )->validationMessages([
                                        'unique' => 'The task name has already been taken.'
                                    ]),
                                // Select::make('task_tut_id')
                                //     ->label('Usage Type')
                                //     ->placeholder('Select a usage type')
                                //     ->relationship('taskUsageType', 'tut_name')
                                //     ->searchable()
                                //     ->preload()
                                //     ->required()
                                //     ->native(false)
                                //     ->exists(
                                //         table: TaskUsageType::class,
                                //         column: 'tut_id',
                                //     ),
                            ])
                            ->createOptionModalHeading('Add New Preventive Task')
                            ->createOptionAction(
                                fn(Action $action) => $action
                                    ->modalWidth(Width::Large)
                                    ->mutateFormDataUsing(function (array $data) {
                                        $data['task_tut_id'] = 2;
                                        return $data;
                                    })
                            ),
                        TextInput::make('ets_sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                    ]),

                Section::make('Interval')
                    ->icon('heroicon-o-arrow-path')
                    ->columns(4)
                    ->schema([
                        TextInput::make('ets_itrv_years')
                            ->label('Years')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('ets_itrv_months')
                            ->label('Months')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('ets_itrv_weeks')
                            ->label('Weeks')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('ets_itrv_days')
                            ->label('Days')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(0),
                    ]),

                Section::make('Schedule & Due Dates')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->schema([
                        TimePicker::make('ets_sched_time')
                            ->label('Scheduled Time')
                            ->columnSpan(2)
                            ->required()
                            ->seconds(false),
                        DatePicker::make('ets_due_effectivity_dt')
                            ->label('Due Effectivity Date')
                            ->columnSpan(1)
                            ->required()
                            ->native(false)
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Schedule Details')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('task.task_name')
                            ->label('Task'),
                        TextEntry::make('ets_sort_order')
                            ->label('Sort Order'),
                        TextEntry::make('department.dep_name')
                            ->label('Department')
                            ->visible(fn() => auth()->user()->hasRole('super_admin')),
                    ]),

                Section::make('Interval')
                    ->icon('heroicon-o-arrow-path')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('ets_itrv_years')
                            ->label('Years')
                            ->placeholder('-'),
                        TextEntry::make('ets_itrv_months')
                            ->label('Months')
                            ->placeholder('-'),
                        TextEntry::make('ets_itrv_weeks')
                            ->label('Weeks')
                            ->placeholder('-'),
                        TextEntry::make('ets_itrv_days')
                            ->label('Days')
                            ->placeholder('-'),
                    ]),

                Section::make('Schedule & Due Dates')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('ets_sched_time')
                            ->label('Scheduled Time')
                            ->time('h:i A')
                            ->placeholder('-'),
                        TextEntry::make('ets_due_effectivity_dt')
                            ->label('Due Effectivity Date')
                            ->dateTime('M d, Y | h:i A'),
                        TextEntry::make('ets_due_dt')
                            ->label('Due Date')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('-'),
                    ]),

                Section::make('Assignment')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('assignedBy.user_fname')
                            ->label('Assigned By')
                            ->placeholder('-'),
                        TextEntry::make('ets_assigned_at')
                            ->label('Assigned At')
                            ->dateTime('M d, Y | h:i A'),
                        TextEntry::make('lastAssignedBy.user_fname')
                            ->label('Last Assigned By')
                            ->placeholder('-'),
                        TextEntry::make('ets_last_assigned_at')
                            ->label('Last Assigned At')
                            ->dateTime('M d, Y | h:i A'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ets_id')
            ->columns([
                TextColumn::make('task.task_name')
                    ->label('Task')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->sortable()
                    ->visible(fn() => auth()->user()->hasRole('super_admin')),
                TextColumn::make('ets_due_effectivity_dt')
                    ->label('Effectivity Date')
                    ->dateTime('M d, Y')
                    ->sortable(),
                TextColumn::make('ets_due_dt')
                    ->label('Due Date')
                    ->dateTime('M d, Y | h:i A')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('ets_sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('ets_dep_id')
                    ->label('Department')
                    ->relationship('department', 'dep_name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create Preventive Task')
                    ->modalHeading('Create Equipment Task Schedule')
                    ->modalWidth(Width::SevenExtraLarge)
                    ->authorize(true)
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
            ])
            ->recordAction('view')
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('View Task Schedule')
                    ->modalWidth(Width::SevenExtraLarge),
                EditAction::make()
                    ->authorize(true)
                    ->modalHeading('Edit Task Schedule')
                    ->modalWidth(Width::SevenExtraLarge)
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false),
                DeleteAction::make()
                    ->authorize(true)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorize(true),
                ]),
            ]);
        // ->extraAttributes([
        //     'style' => 'margin-top: 30px;'
        // ]);
    }
}

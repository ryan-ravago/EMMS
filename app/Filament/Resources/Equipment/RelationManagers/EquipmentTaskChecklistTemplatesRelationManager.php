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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class EquipmentTaskChecklistTemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'equipmentTaskChecklistTemplates';

    protected static ?string $title = 'Tasks for Inspection';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['etct_eqm_id'] = $this->getOwnerRecord()->getKey();
        $data['etct_dep_id'] = auth()->user()->user_dep_id;
        $data['etct_created_by'] = auth()->id();
        $data['etct_created_at'] = now();

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ets_task_id')
                    ->label('Task')
                    ->relationship(
                        name: 'task',
                        titleAttribute: 'task_name',
                        modifyQueryUsing: fn($query) => $query->where('task_tut_id', 1)
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
                    ->createOptionModalHeading('Add New Inspection Task')
                    ->createOptionAction(
                        fn(Action $action) => $action
                            ->modalWidth(Width::Large)
                            ->mutateFormDataUsing(function (array $data) {
                                $data['task_tut_id'] = 1;
                                return $data;
                            })
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('etct_id')
            ->columns([
                TextColumn::make('task.task_name')
                    ->label('Task')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('createdBy.user_fname')
                    ->label('Added By')
                    ->formatStateUsing(
                        fn($record) => $record->createdBy
                            ? "{$record->createdBy->user_fname} {$record->createdBy->user_lname}"
                            : '—'
                    ),
                TextColumn::make('etct_created_at')
                    ->label('Added At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create Inspection Task')
                    ->modalHeading('Create Equipment Inspection Task')
                    ->modalWidth('lg')
                    ->authorize(true)
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make()
                        ->authorize(true),
                ]),
            ]);
        // ->extraAttributes([
        //     'style' => 'margin-top: 30px;'
        // ]);
    }
}

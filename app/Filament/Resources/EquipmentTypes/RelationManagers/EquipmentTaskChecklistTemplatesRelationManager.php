<?php

namespace App\Filament\Resources\EquipmentTypes\RelationManagers;

use App\Models\EquipmentTaskChecklistTemplate;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class EquipmentTaskChecklistTemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'equipmentTaskChecklistTemplates';

    protected static ?string $title = 'Checklist Template';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $query = $ownerRecord->equipmentTaskChecklistTemplates();

        if (! Auth::user()->hasRole('super_admin')) {
            $query->where('etct_dep_id', Auth::user()->user_dep_id);
        }

        return (string) $query->count();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['etct_eqmt_id'] = $this->getOwnerRecord()->getKey();
        $data['etct_dep_id'] = Auth::user()->user_dep_id;
        $data['etct_created_by'] = Auth::id();
        $data['etct_created_at'] = now();

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('etct_task_id')
                    ->label('Task')
                    ->relationship(
                        name: 'task',
                        titleAttribute: 'task_name',
                        modifyQueryUsing: fn($query) => $query
                            ->where('task_tut_id', 1)
                            ->where('task_dep_id', Auth::user()->user_dep_id)
                            ->whereNotIn(
                                'task_id',
                                EquipmentTaskChecklistTemplate::query()
                                    ->where('etct_eqmt_id', $this->getOwnerRecord()->getKey())
                                    ->where('etct_dep_id', Auth::user()->user_dep_id)
                                    ->pluck('etct_task_id')
                            )
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
                                modifyRuleUsing: fn(Unique $rule) => $rule->where('task_dep_id', Auth::user()->user_dep_id),
                                ignoreRecord: true,
                            )->validationMessages([
                                'unique' => 'The task name has already been taken.',
                            ]),
                    ])
                    ->createOptionModalHeading('New Checklist Task')
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

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task Details')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('task.task_name')
                            ->label('Task Name')
                            ->columnSpanFull(),
                        TextEntry::make('task.department.dep_name')
                            ->label('Department'),
                        TextEntry::make('task.taskUsageType.tut_name')
                            ->label('Usage Type'),
                    ]),

                Section::make('Audit Info')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('creator.user_fname')
                            ->label('Added By')
                            ->formatStateUsing(fn($record) => $record->creator
                                ? "{$record->creator->user_fname} {$record->creator->user_lname}"
                                : '—'),
                        TextEntry::make('etct_created_at')
                            ->label('Added At')
                            ->dateTime('M d, Y | h:i A'),
                    ]),
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
                TextColumn::make('creator.user_fname')
                    ->label('Added By')
                    ->formatStateUsing(
                        fn($record) => $record->creator
                            ? "{$record->creator->user_fname} {$record->creator->user_lname}"
                            : '—'
                    ),
                TextColumn::make('etct_created_at')
                    ->label('Added At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if (! Auth::user()->hasRole('super_admin')) {
                    $query->where('etct_dep_id', Auth::user()->user_dep_id);
                }
            })
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->authorize(fn() => Auth::user()->can('create', EquipmentTaskChecklistTemplate::class))
                    ->label('Add Checklist Task')
                    ->modalHeading('Add Equipment Checklist Task')
                    ->modalWidth('lg')
                    // ->authorize(true)
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::FiveExtraLarge),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make()
                    ->authorize(true),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make()
                        ->authorize(true),
                ]),
            ]);
    }
}

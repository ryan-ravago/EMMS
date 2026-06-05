<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\Inspections\InspectionResource;
use App\Models\Action;
use App\Models\Department;
use App\Models\EquipmentTaskChecklistTemplate;
use App\Models\InspectionItem;
use App\Models\InspectionItemLog;
use App\Models\Status;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InspectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'inspections';

    // protected static ?string $relatedResource = InspectionResource::class;

    protected function now(): \Illuminate\Support\Carbon
    {
        return $this->now ??= now();
    }

    // InspectionsRelationManager
    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->inspections()->count();
    }

    // protected function handleRecordCreation(array $data): Model
    // {
    //     return DB::transaction(function () use ($data) {
    //         try {
    //             $items = $data['inspection_items'] ?? [];
    //             unset($data['inspection_items']);

    //             if (empty($items)) {
    //                 Notification::make()
    //                     ->title('No inspection items found')
    //                     ->body('Please ensure the equipment has a checklist template configured.')
    //                     ->danger()
    //                     ->send();

    //                 $this->halt();
    //             }

    //             $inspection = $this->getRelationship()->create([
    //                 ...$data,
    //                 'ins_eqm_id'       => $this->getOwnerRecord()->getKey(),
    //                 'ins_dep_id'       => auth()->user()->hasRole('super_admin')
    //                     ? $data['ins_dep_id']
    //                     : auth()->user()->user_dep_id,
    //                 'ins_submitted_by' => Auth::id(),
    //                 'ins_submitted_dt' => now(),
    //             ]);

    //             $itemsToInsert = collect($items)->map(fn($item) => [
    //                 'insi_ins_id'              => $inspection->ins_id,
    //                 'insi_task_id'             => $item['insi_task_id'],
    //                 'insi_cli_name_for_record' => $item['insi_cli_name_for_record'],
    //                 'insi_result'              => $item['insi_result'],
    //                 'insi_remarks'             => $item['insi_remarks'] ?? null,
    //             ])->toArray();

    //             DB::table('inspection_items')->insert($itemsToInsert);

    //             return $inspection;
    //         } catch (\Exception $e) {
    //             Log::error('Inspection creation failed', [
    //                 'error' => $e->getMessage(),
    //                 'data'  => $data,
    //             ]);

    //             Notification::make()
    //                 ->title('Failed to save inspection')
    //                 ->body($e->getMessage())
    //                 ->danger()
    //                 ->send();

    //             $this->halt();
    //         }
    //     });
    // }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inspection Details')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('department.dep_name')
                            ->label('Department')
                            ->visible(fn() => auth()->user()->hasRole('super_admin')),

                        TextEntry::make('equipment.eqm_name')
                            ->label('Equipment'),

                        TextEntry::make('conductedBy.user_fname')
                            ->label('Inspected By')
                            ->formatStateUsing(fn($record) => "{$record->conductedBy->user_fname} {$record->conductedBy->user_lname}"),

                        TextEntry::make('ins_dt')
                            ->label('Inspection Date & Time')
                            ->dateTime('M d, Y | h:i A'),

                        TextEntry::make('submittedBy.user_fname')
                            ->label('Submitted By')
                            ->formatStateUsing(fn($record) => $record->submittedBy
                                ? "{$record->submittedBy->user_fname} {$record->submittedBy->user_lname}"
                                : '—'),

                        TextEntry::make('ins_submitted_dt')
                            ->label('Submitted At')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('—'),
                    ]),

                Section::make('Inspection Items')
                    ->icon('heroicon-o-list-bullet')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('inspectionItems')
                            ->label('')
                            ->columns(2)
                            ->grid(3)
                            ->schema([
                                TextEntry::make('insi_cli_name_for_record')
                                    ->label('Task')
                                    ->columnSpan(1),

                                TextEntry::make('insi_result')
                                    ->label('Result')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'P' => 'success',
                                        'F' => 'danger',
                                        'N' => 'gray',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'P' => 'Passed',
                                        'F' => 'Failed',
                                        'N' => 'N/A',
                                        default => '—',
                                    })
                                    ->columnSpan(1),

                                TextEntry::make('status.status_title')
                                    ->label('Status')
                                    ->badge()
                                    ->columnSpan(1)
                                    ->color(fn(InspectionItem $record) => $record->status->status_color)
                                    ->icon(fn(InspectionItem $record) => $record->status->status_icon),

                                TextEntry::make('insi_remarks')
                                    ->label('Remarks')
                                    ->placeholder('—')
                                    ->columnSpan(2),

                                TextEntry::make('view_link')
                                    ->hiddenLabel()
                                    ->default('View details →')
                                    ->url(fn($record) => url("/inspection-items/{$record->insi_id}"))
                                    ->columnSpan(2)
                                    // ->openUrlInNewTab()
                                    ->extraAttributes([
                                        'class' => 'text-right fi-link text-primary-600 hover:text-primary-500 hover:underline font-medium cursor-pointer',
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipment.eqm_name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->visible(fn() => auth()->user()->hasRole('super_admin')),
                TextColumn::make('conductedBy.user_fname')
                    ->label('Inspected by')
                    ->formatStateUsing(fn($record) => "{$record->conductedBy->user_fname} {$record->conductedBy->user_lname}"),
                TextColumn::make('ins_dt')
                    ->label('Inspection Date')
                    ->dateTime('M d, Y | h:i A')
                    ->sortable(),
                TextColumn::make('submittedBy.user_fname')
                    ->label('Submitted by')
                    ->formatStateUsing(fn($record) => $record->submittedBy
                        ? "{$record->submittedBy->user_fname} {$record->submittedBy->user_lname}"
                        : '—'),
                TextColumn::make('ins_submitted_dt')
                    ->label('Submitted at')
                    ->dateTime('M d, Y | h:i A')
                    ->sortable(),
                TextColumn::make('inspection_items_count')
                    ->label('Items Count')
                    ->counts('inspectionItems')
                    ->badge(),
            ])
            ->defaultSort('ins_dt', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->slideOver(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->authorize(true)
                    ->schema([
                        Section::make('Inspection Details')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('ins_dep_id')
                                    ->label('Department')
                                    ->options(Department::pluck('dep_name', 'dep_id'))
                                    ->required(fn() => auth()->user()->hasRole('super_admin'))
                                    ->disabled(fn() => !auth()->user()->hasRole('super_admin'))
                                    ->dehydrated(fn() => !auth()->user()->hasRole('super_admin'))
                                    ->native(false)
                                    ->searchable()
                                    // ->visible(fn() => auth()->user()->hasRole('super_admin'))
                                    ->default(fn() => auth()->user()->hasRole('super_admin') ? null : auth()->user()->user_dep_id)
                                    ->live(),

                                Select::make('ins_eqm_id')
                                    ->label('Equipment')
                                    ->hint('Only equipment with templates are shown')
                                    ->hintColor('gray')
                                    ->required()
                                    ->preload()
                                    ->native(false)
                                    ->searchable()
                                    ->default(fn() => $this->getOwnerRecord()->getKey())
                                    ->disabled()
                                    ->dehydrated()
                                    ->relationship(
                                        name: 'equipment',
                                        titleAttribute: 'eqm_name',
                                        modifyQueryUsing: function ($query, Get $get) {
                                            $depId = auth()->user()->hasRole('super_admin')
                                                ? $get('ins_dep_id')
                                                : auth()->user()->user_dep_id;

                                            return $query
                                                ->where('eqm_is_active', true)
                                                ->whereHas('equipmentTaskChecklistTemplates', fn($q) => $q->where('etct_dep_id', $depId));
                                        }
                                    )
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $depId = auth()->user()->hasRole('super_admin')
                                            ? $get('ins_dep_id')
                                            : auth()->user()->user_dep_id;

                                        if (!$state || !$depId) {
                                            $set('inspection_items', []);
                                            return;
                                        }

                                        $items = EquipmentTaskChecklistTemplate::with('task')
                                            ->where('etct_eqm_id', $state)
                                            ->where('etct_dep_id', $depId)
                                            ->get()
                                            ->map(fn($template) => [
                                                'insi_cli_name_for_record' => $template->task->task_name,
                                                'insi_task_id'             => $template->etct_task_id,
                                                'insi_result'              => null,
                                                'insi_remarks'             => null,
                                            ])
                                            ->toArray();

                                        $set('inspection_items', $items);
                                    }),

                                Select::make('ins_by')
                                    ->label('Inspected By')
                                    ->required()
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->relationship(
                                        name: 'conductedBy',
                                        titleAttribute: 'user_fname',
                                        modifyQueryUsing: function ($query, Get $get) {
                                            $depId = auth()->user()->hasRole('super_admin')
                                                ? $get('ins_dep_id')
                                                : auth()->user()->user_dep_id;

                                            return $query
                                                ->whereHas('roles', fn($q) => $q->where('name', 'technician'))
                                                ->where('user_dep_id', $depId);
                                        }
                                    )
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->user_fname} {$record->user_lname}"),

                                DateTimePicker::make('ins_dt')
                                    ->label('Inspection Date & Time')
                                    ->required()
                                    ->seconds(false)
                                    ->displayFormat('M d, Y | h:i A')
                                    ->default(now())
                                    ->maxDate(now()),
                            ]),

                        Section::make('Inspection Tasks')
                            ->icon('heroicon-o-list-bullet')
                            ->visible(fn(Get $get) => filled($get('ins_eqm_id')))
                            ->columnSpanFull()
                            ->schema([
                                Repeater::make('inspection_items')
                                    ->hiddenLabel()
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->columns(2)
                                    ->grid(3)
                                    ->default(function (Get $get) {
                                        $eqmId = $this->getOwnerRecord()->getKey();
                                        $depId = auth()->user()->hasRole('super_admin')
                                            ? $get('ins_dep_id')
                                            : auth()->user()->user_dep_id;

                                        if (!$eqmId || !$depId) return [];

                                        return EquipmentTaskChecklistTemplate::with('task')
                                            ->where('etct_eqm_id', $eqmId)
                                            ->where('etct_dep_id', $depId)
                                            ->get()
                                            ->map(fn($template) => [
                                                'insi_cli_name_for_record' => $template->task->task_name,
                                                'insi_task_id'             => $template->etct_task_id,
                                                'insi_result'              => null,
                                                'insi_remarks'             => null,
                                            ])
                                            ->toArray();
                                    })
                                    ->schema([
                                        TextInput::make('insi_cli_name_for_record')
                                            ->label('Task')
                                            ->dehydrated()
                                            ->disabled()
                                            ->columnSpan(2),
                                        ToggleButtons::make('insi_result')
                                            ->label('Result')
                                            ->options([
                                                'P' => 'Passed',
                                                'F' => 'Failed',
                                                'N' => 'N/A',
                                            ])
                                            ->colors([
                                                'P' => 'success',
                                                'F' => 'danger',
                                                'N' => 'gray',
                                            ])
                                            ->icons([
                                                'P' => 'heroicon-o-check-circle',
                                                'F' => 'heroicon-o-x-circle',
                                                'N' => 'heroicon-o-minus-circle',
                                            ])
                                            ->required()
                                            ->inline()
                                            ->columnSpan(2),
                                        // Select::make('insi_result')
                                        //     ->label('Result')
                                        //     ->options([
                                        //         'P' => 'Passed',
                                        //         'F' => 'Failed',
                                        //         'N' => 'N/A',
                                        //     ])
                                        //     ->required()
                                        //     ->native(false)
                                        //     ->live()
                                        //     ->columnSpan(1),
                                        Textarea::make('insi_remarks')
                                            ->label('Remarks')
                                            ->nullable()
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                    ])
                    ->modalWidth(Width::SevenExtraLarge)
                    // ->slideOver()
                    ->action(function (array $data, CreateAction $action) use ($table) {
                        DB::transaction(function () use ($data, $action, $table) {
                            try {
                                $items = $data['inspection_items'] ?? [];
                                unset($data['inspection_items']);

                                if (empty($items)) {
                                    Notification::make()
                                        ->title('No inspection items found')
                                        ->body('Please ensure the equipment has a checklist template configured.')
                                        ->danger()
                                        ->send();

                                    $action->halt();
                                    return;
                                }

                                $ownerRecord = $table->getRelationship()->getParent();

                                $inspection = $table->getRelationship()->create([
                                    ...$data,
                                    'ins_eqm_id'       => $ownerRecord->getKey(),
                                    'ins_dep_id'       => auth()->user()->hasRole('super_admin')
                                        ? $data['ins_dep_id']
                                        : auth()->user()->user_dep_id,
                                    'ins_submitted_by' => auth()->id(),
                                    'ins_submitted_dt' => $this->now(),
                                ]);

                                $action = Action::find('create');
                                $statusPnd = Status::find('pnd');
                                $statusCmp = Status::find('cmp');

                                DB::table('inspection_items')->insert(
                                    collect($items)->map(fn($item) => [
                                        'insi_ins_id'              => $inspection->ins_id,
                                        'insi_task_id'             => $item['insi_task_id'],
                                        'insi_status_id'           => in_array($item['insi_result'], ['P', 'N']) ? 'cmp' : 'pnd',
                                        'insi_cli_name_for_record' => $item['insi_cli_name_for_record'],
                                        'insi_result'              => $item['insi_result'],
                                        'insi_remarks'             => $item['insi_remarks'] ?? null,
                                        'insi_closed_dt'           => in_array($item['insi_result'], ['P', 'N']) ? $this->now() : null,
                                    ])->toArray()
                                );

                                $createdItems = InspectionItem::where('insi_ins_id', $inspection->ins_id)->get();

                                $logs = $createdItems->map(fn($item) => [
                                    'inil_insi_id'     => $item->insi_id,
                                    'inil_a_id'        => $action->a_id,
                                    'inil_status_id'   => $item->insi_status_id === 'cmp' ? $statusCmp->status_id : $statusPnd->status_id,
                                    'inil_action_made' => $action->a_past_tense,
                                    'inil_status_log'  => $item->insi_status_id === 'cmp' ? $statusCmp->status_title : $statusPnd->status_title,
                                    'inil_remarks'     => null,
                                    'inil_by'          => auth()->id(),
                                    'inil_dt'          => $this->now(),
                                ])->toArray();

                                InspectionItemLog::insert($logs);
                            } catch (\Exception $e) {
                                Log::error('Inspection creation failed', [
                                    'error' => $e->getMessage(),
                                    'data'  => $data,
                                ]);

                                Notification::make()
                                    ->title('Failed to save inspection')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }
                        });
                    })
            ]);
    }
}

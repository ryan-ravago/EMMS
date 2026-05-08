<?php

namespace App\Filament\Resources\ChecklistTemplates\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use Carbon\Carbon;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\Actions\Action as HintAction;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class EquipmentUnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'equipmentUnits';

    // protected static ?string $relatedResource = EquipmentResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eqm_prc_code')
                    ->label('PRC Code')
                    ->searchable(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('model.eqmm_name')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('eqm_vin')
                    ->label('VIN')
                    ->searchable(),
                TextColumn::make('eqm_plate_num')
                    ->label('Plate #')
                    ->searchable()
            ])
            ->headerActions([
                // CreateAction::make(),
                // AttachAction::make()
                //     ->multiple()
                //     ->recordSelectSearchColumns(['eqm_name'])
                //     ->recordTitleAttribute('eqm_name')
                //     ->preloadRecordSelect(),
                Action::make('attachByModel')
                    ->label('Attach by Model')
                    ->modalWidth('lg')
                    ->closeModalByClickingAway(false)
                    ->modalCloseButton(false)
                    ->schema([
                        Select::make('eqm_eqmm_id')
                            ->label('Filter by Model')
                            ->options(EquipmentModel::pluck('eqmm_name', 'eqmm_id'))
                            ->searchable()
                            ->live()
                            ->placeholder('Select a model...')
                            ->afterStateUpdated(fn(Set $set) => $set('equipment_ids', [])),
                        Select::make('equipment_ids')
                            ->label('Equipment Units')
                            ->multiple()
                            ->required()
                            ->preload()
                            ->options(function (Get $get) {
                                $modelId = $get('eqm_eqmm_id');
                                $attached = $this->getOwnerRecord()->equipmentUnits()->pluck('eqm_id')->toArray();

                                return Equipment::when(
                                    $modelId,
                                    fn($q) => $q->where('eqm_eqmm_id', $modelId)
                                )
                                    ->whereNotIn('eqm_id', $attached)
                                    ->pluck('eqm_name', 'eqm_id');
                            })
                            ->live()
                            ->hintActions([
                                Action::make('select_all')
                                    ->label('Select All')
                                    ->icon('heroicon-o-check-circle')
                                    ->action(function (Get $get, Set $set): void {
                                        $modelId = $get('eqm_eqmm_id');
                                        $attached = $this->getOwnerRecord()->equipmentUnits()->pluck('eqm_id')->toArray();

                                        $ids = Equipment::when(
                                            $modelId,
                                            fn($q) => $q->where('eqm_eqmm_id', $modelId)
                                        )
                                            ->whereNotIn('eqm_id', $attached)
                                            ->pluck('eqm_id')
                                            ->toArray();

                                        $set('equipment_ids', $ids);
                                    }),
                                Action::make('clear')
                                    ->label('Clear')
                                    ->icon('heroicon-o-x-circle')
                                    ->color('danger')
                                    ->action(fn(Set $set) => $set('equipment_ids', [])),
                            ]),
                        DateTimePicker::make('eca_due_effectivity_dt')
                            ->label('Effectivity Date & Time')
                            ->seconds(false)
                            ->displayFormat('M j, Y h:i A')
                            ->visible(fn() => $this->getOwnerRecord()->clt_cut_id === 2)
                            ->native(false),
                    ])
                    ->action(function (array $data): void {
                        try {
                            $owner = $this->getOwnerRecord();
                            $existingIds = $owner->equipmentUnits()->pluck('eqm_id')->toArray();
                            $newIds = array_diff($data['equipment_ids'], $existingIds);

                            if (empty($newIds)) {
                                Notification::make()
                                    ->warning()
                                    ->title('No New Equipment')
                                    ->body('All selected equipment units are already attached.')
                                    ->send();
                                return;
                            }

                            $pivotTable = $owner->equipmentUnits()->getTable();
                            $foreignKey = $owner->equipmentUnits()->getForeignPivotKeyName();
                            $relatedKey = $owner->equipmentUnits()->getRelatedPivotKeyName();

                            $dueEffectivityDt = null;
                            $dueDt = null;

                            if ($owner->clt_cut_id === 2 && !empty($data['eca_due_effectivity_dt'])) {
                                $dueEffectivityDt = $data['eca_due_effectivity_dt'];

                                $dueDt = Carbon::parse($dueEffectivityDt)
                                    ->addYears($owner->clt_interval_years ?? 0)
                                    ->addMonths($owner->clt_interval_months ?? 0)
                                    ->addWeeks($owner->clt_interval_weeks ?? 0)
                                    ->addDays($owner->clt_interval_days ?? 0);

                                if (!empty($owner->clt_schedule_time)) {
                                    $scheduleTime = Carbon::parse($owner->clt_schedule_time);
                                    $dueDt->setTime($scheduleTime->hour, $scheduleTime->minute);
                                }
                            }

                            $rows = array_map(fn($id) => [
                                $foreignKey => $owner->getKey(),
                                $relatedKey => $id,
                                'eca_due_effectivity_dt' => $dueEffectivityDt,
                                'eca_due_dt' => $dueDt,
                                'eca_assigned_by' => auth()->id(),
                                'eca_assigned_at' => now(),
                            ], $newIds);

                            collect($rows)->chunk(500)->each(
                                fn($chunk) => DB::table($pivotTable)->insert($chunk->toArray())
                            );

                            Notification::make()
                                ->success()
                                ->title('Equipment Attached')
                                ->body(count($newIds) . ' equipment unit(s) successfully attached.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Attachment Failed')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->recordUrl(fn(Equipment $record) => EquipmentResource::getUrl('view', ['record' => $record->eqm_id]))
            ->openRecordUrlInNewTab()
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ])
            ->extraAttributes([
                'style' => 'margin-top: 30px;'
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}

<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use App\Models\LifecycleLog;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Validation\ValidationException;

class AccessoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'accessories';

    protected static ?string $inverseRelationship = 'parent';

    protected static ?string $title = 'Accessories';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->accessories()->count();

        return (string) $count;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Equipment
            && parent::canViewForRecord($ownerRecord, $pageClass)
            && $ownerRecord->isEquipmentAsset();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('eqm_name')
            ->columns([
                TextColumn::make('eqm_prc_code')
                    ->label('Asset Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipmentModel.eqmm_name')
                    ->label('Model')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('eqm_is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->icon(fn (bool $state): Heroicon => $state ? Heroicon::CheckCircle : Heroicon::XCircle)
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Allocate accessory')
                    ->modalHeading('Allocate accessory')
                    ->modalSubmitActionLabel('Allocate')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['eqm_name', 'eqm_prc_code'])
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query->accessories())
                    ->before(function (AssociateAction $action, array $data) {
                        $selectedId = $data['recordId'] ?? null;
                        $ownerRecord = $this->getOwnerRecord();

                        // 1. Check if the submitted record exists and passes the accessories scope
                        $isValidAccessory = Equipment::query()
                            ->accessories()
                            ->where('eqm_id', $selectedId)
                            ->exists();

                        if (! $isValidAccessory) {
                            throw ValidationException::withMessages([
                                'recordId' => 'The selected record is not a valid accessory.',
                            ]);
                        }

                        // 2. Prevent self-association (equipping an asset to itself)
                        if ((int) $selectedId === (int) $ownerRecord->getKey()) {
                            throw ValidationException::withMessages([
                                'recordId' => 'An asset cannot be associated with itself as an accessory.',
                            ]);
                        }

                        // 3. Optional: Prevent re-associating an accessory already assigned to another asset
                        $alreadyAssigned = Equipment::query()
                            ->where('eqm_id', $selectedId)
                            ->whereNotNull('parent_id')
                            ->where('parent_id', '!=', $ownerRecord->getKey())
                            ->exists();

                        if ($alreadyAssigned) {
                            throw ValidationException::withMessages([
                                'recordId' => 'This accessory is already allocated to another equipment unit.',
                            ]);
                        }
                    })
                    ->using(function (Model $record, BelongsTo $inverseRelationship, HasMany $relationship): void {
                        DB::transaction(function () use ($record, $inverseRelationship, $relationship): void {
                            $inverseRelationship->associate($relationship->getParent());
                            $record->save();

                            $this->createLifecycleLog($record, 'alc', 'alc', $relationship->getParent()->getKey());
                        });
                    }),
            ])
            ->recordActions([
                DissociateAction::make()
                    ->label('Unallocate')
                    ->using(function (Model $record, Table $table): void {
                        DB::transaction(function () use ($record, $table): void {
                            $inverseRelationship = $table->getInverseRelationshipFor($record);
                            $inverseRelationship->dissociate();
                            $record->save();

                            $this->createLifecycleLog($record, 'sidle', 'idle');
                        });
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Unallocate')
                        ->using(function (
                            DissociateBulkAction $action,
                            EloquentCollection|Collection|LazyCollection $records,
                            Table $table,
                        ): void {
                            DB::transaction(function () use ($action, $records, $table): void {
                                $accessoryIds = [];

                                $records->each(function (Model $record) use ($table, &$accessoryIds): void {
                                    $inverseRelationship = $table->getInverseRelationshipFor($record);
                                    $inverseRelationship->dissociate();
                                    $record->save();
                                    $accessoryIds[] = $record->getKey();
                                });

                                if ($accessoryIds !== []) {
                                    $this->createLifecycleLogs($accessoryIds, 'sidle', 'idle');
                                }

                                $action->reportBulkProcessingSuccessfulRecordsCount(count($accessoryIds));
                            });
                        }),
                ]),
            ])
            ->recordUrl(
                fn (Equipment $record): string => EquipmentResource::withConfiguration(
                    'accessories',
                    fn (): string => EquipmentResource::getUrl('view', ['record' => $record]),
                ),
            );
    }

    protected function createLifecycleLog(
        Model $accessory,
        string $actionId,
        string $statusId,
        ?int $allocateToEquipmentId = null,
    ): void {
        LifecycleLog::query()->create([
            'asset_id' => $accessory->getKey(),
            'action_id' => $actionId,
            'status_id' => $statusId,
            'allocate_to_equipment_id' => $allocateToEquipmentId,
            'performed_by' => Auth::id(),
            'logged_at' => now(),
        ]);
    }

    /**
     * @param  array<int, int|string>  $accessoryIds
     */
    protected function createLifecycleLogs(array $accessoryIds, string $actionId, string $statusId): void
    {
        $loggedAt = now();

        LifecycleLog::query()->insert(array_map(
            fn (int|string $accessoryId): array => [
                'asset_id' => $accessoryId,
                'action_id' => $actionId,
                'status_id' => $statusId,
                'performed_by' => Auth::id(),
                'logged_at' => $loggedAt,
            ],
            $accessoryIds,
        ));
    }
}

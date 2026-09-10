<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class AccessoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'accessories';

    protected static ?string $inverseRelationship = 'parent';

    protected static ?string $title = 'Accessories';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->accessories()->count();

        return $count ? (string) $count : null;
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
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                    ->icon(fn(bool $state): Heroicon => $state ? Heroicon::CheckCircle : Heroicon::XCircle)
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Allocate accessory')
                    ->modalHeading('Allocate accessory')
                    ->modalSubmitActionLabel('Allocate')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['eqm_name', 'eqm_prc_code'])
                    ->recordSelectOptionsQuery(fn(Builder $query): Builder => $query->accessories())
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
                    }),
            ])
            ->recordActions([
                DissociateAction::make()
                    ->label('Unallocate'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Unallocate'),
                ]),
            ])
            ->recordUrl(
                fn(Equipment $record): string => EquipmentResource::getUrl('view', ['record' => $record]),
            );
    }
}

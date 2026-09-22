<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\AssetEditLog;
use App\Models\Equipment;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AccessoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'equipments';

    protected static ?string $title = 'Accessories';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->equipments()->accessories()->count();
    }

    /**
     * Log a tag (category) add/remove for one equipment as an old/new array diff.
     */
    protected function logCategoryChange(Equipment $equipment, string $categoryName, bool $attached): void
    {
        $currentTags = $equipment->categories()->pluck('eqmc_name')->filter()->values()->all();

        if ($attached) {
            // $currentTags already includes the just-attached category.
            $newTags = $currentTags;
            $oldTags = array_values(array_diff($newTags, [$categoryName]));
        } else {
            // $currentTags already excludes the just-detached category.
            $newTags = $currentTags;
            $oldTags = array_values(array_unique([...$currentTags, $categoryName]));
        }

        AssetEditLog::create([
            'asset_id'     => $equipment->getKey(),
            'performed_by' => Auth::id(),
            'logged_at'    => now(),
            'changes'      => [
                'categories' => [
                    'old' => $oldTags,
                    'new' => $newTags,
                ],
            ],
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('categories')
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->accessories())
            ->recordTitle(
                fn(Equipment $record): string => "{$record->eqm_prc_code} - {$record->eqm_name}"
            )
            ->recordTitleAttribute('eqm_name')
            ->columns([
                TextColumn::make('eqm_prc_code')
                    ->label('Asset Code')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('parent.eqm_name')
                    ->label('Allocated to')
                    ->placeholder('—')
                    ->toggleable()
                    ->searchable()
                    ->visible(
                        fn(): bool => EquipmentResource::getConfiguration()?->getKey() !== 'equipment'
                    ),
                TextColumn::make('eqm_is_active')
                    ->label('Status')
                    ->toggleable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                    ->icon(fn(bool $state): Heroicon => $state ? Heroicon::CheckCircle : Heroicon::XCircle)
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('lifecycleStatus.status_title')
                    ->label('Lifecycle Status')
                    ->toggleable()
                    ->sortable()
                    ->badge()
                    ->icon(fn($record) => $record->lifecycleStatus?->status_icon)
                    ->color(fn($record) => $record->lifecycleStatus?->status_color),
                TextColumn::make('location.name')
                    ->label('Location')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
            ])
            ->recordUrl(
                fn(Equipment $record): string => EquipmentResource::withConfiguration(
                    'accessories',
                    fn(): string => EquipmentResource::getUrl('view', ['record' => $record]),
                ),
            )
            ->headerActions([
                AttachAction::make('associate_accessories_to_tag')
                    ->authorize(fn(): bool => Auth::user()->hasPermissionTo('Update:EquipmentResource'))
                    ->label('Associate')
                    ->modalHeading('Associate Accessories to Tag')
                    ->modalSubmitActionLabel('Associate')
                    ->preloadRecordSelect()
                    ->multiple()
                    ->recordSelectSearchColumns(['eqm_name', 'eqm_prc_code'])
                    ->recordSelectOptionsQuery(
                        fn(Builder $query): Builder => $query
                            ->accessories()
                            ->whereDoesntHave(
                                'categories',
                                fn(Builder $q) => $q->whereKey($this->getOwnerRecord()->getKey()),
                            )
                    )
                    ->before(function (array $data): void {
                        $accessoryIds = (array) ($data['recordId'] ?? []);
                        $category = $this->getOwnerRecord();

                        if (empty($accessoryIds)) {
                            return;
                        }

                        $accessories = Equipment::query()
                            ->accessories()
                            ->whereIn('eqm_id', $accessoryIds)
                            ->get();

                        if ($accessories->count() !== count($accessoryIds)) {
                            throw ValidationException::withMessages([
                                'recordId' => 'One or more selected records are not valid accessories.',
                            ]);
                        }

                        $inactive = $accessories->filter(fn(Equipment $item) => ! $item->eqm_is_active);

                        if ($inactive->isNotEmpty()) {
                            throw ValidationException::withMessages([
                                'recordId' => 'Inactive accessories cannot be assigned (' . $inactive->pluck('eqm_name')->implode(', ') . ').',
                            ]);
                        }

                        $alreadyAttachedIds = $category->equipments()
                            ->whereIn('equipment_units.eqm_id', $accessoryIds)
                            ->pluck('equipment_units.eqm_id')
                            ->toArray();

                        if (! empty($alreadyAttachedIds)) {
                            $duplicates = $accessories->whereIn('eqm_id', $alreadyAttachedIds)->pluck('eqm_name')->implode(', ');

                            throw ValidationException::withMessages([
                                'recordId' => "The following accessories are already attached: {$duplicates}.",
                            ]);
                        }
                    })
                    ->after(function (array $data): void {
                        $accessoryIds = (array) ($data['recordId'] ?? []);
                        if (empty($accessoryIds)) {
                            return;
                        }

                        $categoryName = $this->getOwnerRecord()->eqmc_name;

                        Equipment::whereIn('eqm_id', $accessoryIds)
                            ->get()
                            ->each(fn(Equipment $equipment) => $this->logCategoryChange($equipment, $categoryName, attached: true));
                    }),
            ])
            ->recordActions([
                DetachAction::make()
                    ->after(fn(Equipment $record) => $this->logCategoryChange(
                        $record,
                        $this->getOwnerRecord()->eqmc_name,
                        attached: false,
                    )),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->after(function (Collection $records): void {
                            $categoryName = $this->getOwnerRecord()->eqmc_name;

                            $records->each(fn(Equipment $equipment) => $this->logCategoryChange(
                                $equipment,
                                $categoryName,
                                attached: false,
                            ));
                        }),
                ]),
            ]);
    }
}

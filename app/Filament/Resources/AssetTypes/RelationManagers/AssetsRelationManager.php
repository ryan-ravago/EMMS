<?php

namespace App\Filament\Resources\AssetTypes\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'equipmentUnits';

    protected static ?string $title = 'Assets';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->equipmentUnits()->count();
    }

    public function table(Table $table): Table
    {
        return $table
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
                    ->icon(fn($record) => $record->lifecycleStatus->status_icon)
                    ->color(fn($record) => $record->lifecycleStatus->status_color),
                TextColumn::make('location.name')
                    ->label('Location')
                    ->toggleable()
                    ->searchable()
                    ->sortable()
            ])
            ->headerActions([
                AssociateAction::make()
                    ->authorize(fn(): bool => Auth::user()->hasPermissionTo('Update:EquipmentResource'))
                    ->label('Associate assets')
                    ->modalHeading('Add assets')
                    ->modalSubmitActionLabel('Add')
                    ->multiple()
                    ->schema([
                        CheckboxList::make('recordId')
                            ->label('Assets')
                            ->options(fn(): array => Equipment::query()
                                ->where('asset_type_id', '=', null)
                                ->orderBy('eqm_name')
                                ->get()
                                ->mapWithKeys(fn(Equipment $asset): array => [
                                    $asset->getKey() => "{$asset->eqm_prc_code} - {$asset->eqm_name}",
                                ])
                                ->all())
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(1)
                            ->required(),
                    ])
                    ->before(function (array $data): void {
                        $assetIds = array_values(array_filter((array) ($data['recordId'] ?? [])));

                        if ($assetIds === []) {
                            return;
                        }

                        $assets = Equipment::query()
                            ->whereKey($assetIds)
                            ->get(['eqm_id', 'asset_type_id']);

                        if ($assets->count() !== count($assetIds)) {
                            throw ValidationException::withMessages([
                                'recordId' => 'One or more selected assets do not exist.',
                            ]);
                        }

                        if ($assets->contains(fn(Equipment $asset): bool => $asset->asset_type_id !== null)) {
                            throw ValidationException::withMessages([
                                'recordId' => 'One or more selected assets are already associated with an asset type.',
                            ]);
                        }
                    }),
            ])
            ->recordActions([
                DissociateAction::make()
                    ->label('Remove asset'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Remove assets'),
                ]),
            ])
            ->recordUrl(
                fn(Equipment $record): string => EquipmentResource::getUrl('view', ['record' => $record]),
            );
    }
}

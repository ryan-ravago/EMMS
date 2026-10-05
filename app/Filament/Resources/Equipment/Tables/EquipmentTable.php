<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\AssetType;
use App\Models\Equipment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EquipmentTable
{
    /** @var array<int, string>|null */
    private static ?array $assetTypeOptions = null;

    /**
     * Loaded once per request. SelectColumn resolves its options for every row,
     * so a closure that queries directly would run N queries.
     */
    private static function assetTypeOptions(): array
    {
        return self::$assetTypeOptions ??= AssetType::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->extremePaginationLinks()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('eqm_prc_code')
                    ->label('Asset Code')
                    ->toggleable()
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Asset code copied')
                    ->copyMessageDuration(1500)
                    ->disabledClick(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('assetType.name')
                    ->label('Asset Type')
                    ->badge()
                    ->color(fn($record): string => $record->asset_type_id == 1 ? 'primary' : 'success')
                    ->icon(fn($record): Heroicon => $record->asset_type_id == 1 ? Heroicon::OutlinedTruck : Heroicon::OutlinedCog)
                    ->placeholder('—')
                    ->visible(fn(): bool => !Auth::user()->can('Update:EquipmentResource'))
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                SelectColumn::make('asset_type_id')
                    ->label('Asset Type')
                    // ->toggleable()
                    // ->sortable()
                    // ->searchable()
                    ->visible(fn(): bool => Auth::user()->can('Update:EquipmentResource'))
                    ->native(false)
                    ->options(fn(): array => self::assetTypeOptions())
                    ->rules(['nullable', 'exists:asset_types,id']),
                // ->afterStateUpdated(function (mixed $state, Set $set): void {
                //     if ((int) $state !== (int) AssetType::accessoryId()) {
                //         $set('parent_id', null);
                //     }
                // }),
                // TextColumn::make('assetType.name')
                //     ->label('Asset Type')
                //     ->badge()
                //     ->toggleable()
                //     ->sortable(),
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
            // ->defaultSort('eqm_name', 'asc')
            ->filters([
                SelectFilter::make('eqm_is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                SelectFilter::make('asset_type_id')
                    ->label('Asset Type')
                    ->relationship('assetType', 'name'),
                // SelectFilter::make('eqm_eqmm_id')
                //     ->label('Model')
                //     ->relationship('equipmentModel', 'eqmm_name')
                //     ->searchable()
                //     ->preload()
                //     ->multiple(),
            ])
            ->recordUrl(
                fn(Model $record): string => EquipmentResource::getUrl('view', ['record' => $record]),
            )
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

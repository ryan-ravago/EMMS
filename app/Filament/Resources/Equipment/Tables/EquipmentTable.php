<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\AssetType;
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

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                SelectColumn::make('asset_type_id')
                    ->label('Asset Type')
                    // ->toggleable()
                    // ->sortable()
                    // ->searchable()
                    ->visible(fn(): bool => EquipmentResource::getConfiguration() === null)
                    ->native(false)
                    ->optionsRelationship(name: 'assetType', titleAttribute: 'name')
                    ->rules(['required', 'exists:asset_types,id']),
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
                TextColumn::make('equipmentModel.eqmm_name')
                    ->label('Model')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('eqm_plate_num')
                    ->label('Plate #')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('eqm_name', 'asc')
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

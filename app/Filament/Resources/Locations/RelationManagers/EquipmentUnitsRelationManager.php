<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EquipmentUnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'equipmentUnits';

    protected static ?string $title = 'Equipment';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->equipmentUnits()->equipmentAssets()->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->equipmentAssets())
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
                TextColumn::make('assetType.name')
                    ->label('Asset Type')
                    ->badge(),
            ])
            ->recordUrl(fn (Equipment $record): string => EquipmentResource::getUrl('view', ['record' => $record]));
    }
}

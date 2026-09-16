<?php

namespace App\Filament\Resources\Brands\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EquipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'equipments';

    protected static ?string $title = 'Equipment';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->equipments()->equipmentAssets()->count();
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
                TextColumn::make('equipmentModel.eqmm_name')
                    ->label('Model')
                    ->placeholder('—'),
            ])
            ->recordUrl(fn (Equipment $record): string => EquipmentResource::getUrl('view', ['record' => $record]));
    }
}

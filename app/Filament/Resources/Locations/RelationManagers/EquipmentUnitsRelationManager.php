<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
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
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->equipmentAssets())
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
            ->recordUrl(fn(Equipment $record): string => EquipmentResource::getUrl('view', ['record' => $record]));
    }
}

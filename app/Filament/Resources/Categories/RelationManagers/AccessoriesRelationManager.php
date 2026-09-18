<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AccessoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'equipments';

    protected static ?string $title = 'Accessories';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->equipments()->accessories()->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->accessories())
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
            ->recordUrl(
                fn(Equipment $record): string => EquipmentResource::withConfiguration(
                    'accessories',
                    fn(): string => EquipmentResource::getUrl('view', ['record' => $record]),
                ),
            );
    }
}

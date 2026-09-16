<?php

namespace App\Filament\Resources\EquipmentTypes\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Filament\Resources\RelationManagers\RelationManager;
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->accessories())
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
                TextColumn::make('parent.eqm_name')
                    ->label('Allocated To')
                    ->placeholder('Unallocated'),
            ])
            ->recordUrl(
                fn (Equipment $record): string => EquipmentResource::withConfiguration(
                    'accessories',
                    fn (): string => EquipmentResource::getUrl('view', ['record' => $record]),
                ),
            );
    }
}

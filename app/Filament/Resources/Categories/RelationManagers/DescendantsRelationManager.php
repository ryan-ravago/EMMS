<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\EquipmentCategory;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class DescendantsRelationManager extends RelationManager
{
    protected static string $relationship = 'descendants';

    protected static ?string $title = 'Descendants';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) count($ownerRecord->getDescendantIds());
    }

    public function getRelationship(): Relation
    {
        $relationship = Relation::noConstraints(
            fn (): Relation => $this->getOwnerRecord()->children(),
        );

        $relationship->getQuery()->whereKey($this->getOwnerRecord()->getDescendantIds());

        return $relationship;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('eqmc_name')
            ->columns([
                TextColumn::make('eqmc_name')
                    ->label('Tag')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_path')
                    ->label('Path')
                    ->state(fn (EquipmentCategory $record): string => $record->full_path),
            ])
            ->recordUrl(fn (EquipmentCategory $record): string => CategoryResource::getUrl('view', ['record' => $record]));
    }
}

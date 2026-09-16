<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use App\Filament\Resources\Locations\LocationResource;
use App\Models\Location;
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
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_path')
                    ->label('Path')
                    ->state(fn (Location $record): string => $record->full_path),
            ])
            ->recordUrl(fn (Location $record): string => LocationResource::getUrl('view', ['record' => $record]));
    }
}

<?php

namespace App\Filament\Resources\MaintenanceInspections\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Inspection Tasks';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('task')
            ->paginated(false)
            ->columns([
                TextColumn::make('task')
                    ->label('Task'),
                TextColumn::make('result')
                    ->label('Result')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Passed' => 'success',
                        'Failed' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->placeholder('—')
                    ->wrap(),
            ]);
    }
}

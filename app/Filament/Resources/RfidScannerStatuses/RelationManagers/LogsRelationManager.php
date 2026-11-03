<?php

namespace App\Filament\Resources\RfidScannerStatuses\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Every time this scanner changed status (repeated reports of the same status are not kept).
 */
class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'Status history';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->heading('Status changes')
            ->columns([
                TextColumn::make('reported_at')
                    ->label('Reported at')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Became')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('received_at')
                    ->label('Received at')
                    ->dateTime('M d, Y h:i:s A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50]);
    }
}

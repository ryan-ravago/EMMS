<?php

namespace App\Filament\Resources\AimsInspections\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'Activity Log';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->paginated(false)
            ->columns([
                TextColumn::make('logged_at')
                    ->label('When')
                    ->dateTime('M d, Y h:i A'),
                TextColumn::make('action')
                    ->label('Action')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('performed_by')
                    ->label('By')
                    ->placeholder('—'),
                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->placeholder('—')
                    ->wrap(),
            ]);
    }
}

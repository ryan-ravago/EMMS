<?php

namespace App\Filament\Resources\Equipment\Resources\AssetTags\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'Logs';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('id')
                            ->label('Log ID')
                            ->inlineLabel(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'IN' ? 'success' : 'warning')
                            ->inlineLabel(),
                        TextEntry::make('yard.name')
                            ->label('Yard')
                            ->placeholder('—')
                            ->inlineLabel(),
                        TextEntry::make('rssi')
                            ->label('RSSI')
                            ->inlineLabel(),
                        TextEntry::make('detected_at')
                            ->label('Detected At')
                            ->dateTime('M d, Y h:i:s A')
                            ->inlineLabel(),
                        TextEntry::make('received_at')
                            ->label('Received At')
                            ->dateTime('M d, Y h:i:s A')
                            ->inlineLabel(),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('M d, Y h:i:s A')
                            ->inlineLabel(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->heading('Tag Logs')
            ->columns([
                TextColumn::make('detected_at')
                    ->label('Detected At')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable(),
                TextColumn::make('yard.name')
                    ->label('Yard')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'IN' ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('rssi')
                    ->label('RSSI')
                    ->sortable(),
                TextColumn::make('received_at')
                    ->label('Received At')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('detected_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Tag Log Details')
                    ->modalWidth('md'),
            ]);
    }
}

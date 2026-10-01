<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TagLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'tagLogs';

    protected static ?string $title = 'Tag Logs';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->tagLogs()->count();
    }

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
                        TextEntry::make('tag_id')
                            ->label('Tag')
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
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->heading('Tag Logs')
            ->modifyQueryUsing(fn ($query) => $query->with('yard'))
            ->columns([
                TextColumn::make('detected_at')
                    ->label('Detected At')
                    ->dateTime('M d, Y h:i:s A')
                    ->sortable(),
                TextColumn::make('tag_id')
                    ->label('Tag')
                    ->searchable(),
                TextColumn::make('yard.name')
                    ->label('Yard')
                    ->placeholder('—'),
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
            ->filters([
                SelectFilter::make('status')
                    ->options(['IN' => 'IN', 'OUT' => 'OUT']),
            ])
            ->defaultSort('detected_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Tag Log Details')
                    ->modalWidth('md'),
            ]);
    }
}

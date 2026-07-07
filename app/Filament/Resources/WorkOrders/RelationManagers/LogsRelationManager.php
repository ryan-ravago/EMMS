<?php

namespace App\Filament\Resources\WorkOrders\RelationManagers;

use App\Models\WorkOrderLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'History Log';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('wol_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('wol_id')
            ->defaultSort('wol_dt', 'desc')
            ->columns([
                TextColumn::make('wol_a_log')
                    ->label('Last Action'),
                TextColumn::make('wol_status_log')
                    ->label('Status')
                    ->badge()
                    ->color(fn (WorkOrderLog $record): string => $record->status->status_color)
                    ->icon(fn (WorkOrderLog $record): string => $record->status->status_icon),
                TextColumn::make('by.user_fname')
                    ->label('By')
                    ->formatStateUsing(fn ($record) => trim("{$record->by?->user_fname} {$record->by?->user_lname}")),
                TextColumn::make('wol_dt')
                    ->label('Date & Time')
                    ->dateTime('M d, Y | h:i A'),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('View History Log'),
            ])
            ->toolbarActions([]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('wol_a_log')
                            ->label('Last Action')
                            ->columnSpanFull(),
                        TextEntry::make('wol_status_log')
                            ->label('Status')
                            ->badge()
                            ->color(fn (WorkOrderLog $record): string => $record->status->status_color)
                            ->icon(fn (WorkOrderLog $record): string => $record->status->status_icon),
                        TextEntry::make('wol_note')
                            ->label('Note')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('by.user_fname')
                            ->label('By')
                            ->formatStateUsing(fn ($record) => trim("{$record->by?->user_fname} {$record->by?->user_lname}")),
                        TextEntry::make('wol_dt')
                            ->label('Date & Time')
                            ->dateTime('M d, Y | h:i A'),
                    ]),
            ]);
    }
}

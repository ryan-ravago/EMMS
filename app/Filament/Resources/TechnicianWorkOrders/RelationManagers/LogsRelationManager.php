<?php

namespace App\Filament\Resources\TechnicianWorkOrders\RelationManagers;

use App\Models\WorkOrderLog;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    protected static ?string $title = 'History Logs';

    protected function getListeners(): array
    {
        return [
            'refreshLogsRelationManager' => '$refresh',
        ];
    }

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
                    ->color(fn(WorkOrderLog $record): string => $record->status->status_color)
                    ->icon(fn(WorkOrderLog $record): string => $record->status->status_icon),
                TextColumn::make('by.user_fname')
                    ->label('By')
                    ->formatStateUsing(fn($record) => trim("{$record->by?->user_fname} {$record->by?->user_lname}")),
                TextColumn::make('wol_dt')
                    ->label('Date & Time')
                    ->dateTime('M d, Y | h:i A'),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}

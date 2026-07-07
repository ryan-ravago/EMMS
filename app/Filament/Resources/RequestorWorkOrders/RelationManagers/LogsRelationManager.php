<?php

namespace App\Filament\Resources\RequestorWorkOrders\RelationManagers;

use App\Models\Status;
use App\Models\WorkOrderLog;
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

    protected static ?string $title = 'History Log';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
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
                    // ->color(function (WorkOrderLog $record): string {
                    //     // Map "In-Progress" to "Approved" for requestor view
                    //     if ($record->wol_status_id === 'inprog') {
                    //         return Status::find('appr')->status_color ?? 'success';
                    //     }

                    //     return $record->status->status_color;
                    // })
                    // ->icon(function (WorkOrderLog $record): string {
                    //     if ($record->wol_status_id === 'inprog') {
                    //         return Status::find('appr')->status_icon ?? 'heroicon-o-check-badge';
                    //     }

                    //     return $record->status->status_icon;
                    // })
                    // ->state(function (WorkOrderLog $record): string {
                    //     if ($record->wol_status_id === 'inprog') {
                    //         return Status::find('appr')->status_title ?? 'Approved';
                    //     }

                    //     return $record->status->status_title;
                    // })
                    ->color(fn (WorkOrderLog $record) => $record->status->status_color)
                    ->icon(fn (WorkOrderLog $record) => $record->status->status_icon),
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
                            ->color(function (WorkOrderLog $record): string {
                                if ($record->wol_status_id === 'inprog') {
                                    return Status::find('appr')->status_color ?? 'success';
                                }

                                return $record->status->status_color;
                            })
                            ->icon(function (WorkOrderLog $record): string {
                                if ($record->wol_status_id === 'inprog') {
                                    return Status::find('appr')->status_icon ?? 'heroicon-o-check-badge';
                                }

                                return $record->status->status_icon;
                            })
                            ->state(function (WorkOrderLog $record): string {
                                if ($record->wol_status_id === 'inprog') {
                                    return Status::find('appr')->status_title ?? 'Approved';
                                }

                                return $record->status->status_title;
                            }),
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

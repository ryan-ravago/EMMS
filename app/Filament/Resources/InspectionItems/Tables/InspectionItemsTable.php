<?php

namespace App\Filament\Resources\InspectionItems\Tables;

use App\Models\InspectionItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InspectionItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn(InspectionItem $record) => $record->status->status_color)
                    ->icon(fn(InspectionItem $record) => $record->status->status_icon)
                    ->sortable(),
                TextColumn::make('inspection.equipment.eqm_name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('insi_cli_name_for_record')
                    ->label('Task')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('insi_result')
                    ->label('Result')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'P' => 'success',
                        'F' => 'danger',
                        'N' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'P' => 'Passed',
                        'F' => 'Failed',
                        'N' => 'N/A',
                        default => '—',
                    }),
                TextColumn::make('insi_remarks')
                    ->label('Remarks')
                    ->placeholder('—')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->insi_remarks),
                TextColumn::make('insi_closed_dt')
                    ->label('Closed at')
                    ->dateTime('M d, Y | h:i A')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('inspection.ins_submitted_dt')
                    ->label('Timestamp')
                    ->dateTime('M d, Y | h:i A')
                    ->sortable(),
                // TextColumn::make('logs_count')
                //     ->label('Logs')
                //     ->counts('logs')
                //     ->badge()
                //     ->color('info'),
            ])
            ->defaultSort('inspection.ins_submitted_dt', 'desc')
            ->filters([
                SelectFilter::make('insi_result')
                    ->label('Result')
                    ->options([
                        'P' => 'Passed',
                        'F' => 'Failed',
                        'N' => 'N/A',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

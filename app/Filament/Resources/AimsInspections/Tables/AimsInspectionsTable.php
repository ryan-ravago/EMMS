<?php

namespace App\Filament\Resources\AimsInspections\Tables;

use App\Models\AimsInspection;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AimsInspectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('closed_at', 'desc')
            ->columns([
                TextColumn::make('inspection_no')
                    ->label('Inspection #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipment_name')
                    ->label('Equipment')
                    ->description(fn (AimsInspection $record): ?string => $record->equipment_prc_code)
                    ->placeholder('—')
                    ->searchable(['equipment_name', 'equipment_prc_code']),
                TextColumn::make('lessee')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('jobsite')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('operator')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('day_duration')
                    ->label('Days')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        AimsInspection::STATUS_COMPLETED => 'success',
                        AimsInspection::STATUS_CANCELLED => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('inspected_at')
                    ->label('Inspected On')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Closed On')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        AimsInspection::STATUS_COMPLETED => AimsInspection::STATUS_COMPLETED,
                        AimsInspection::STATUS_CANCELLED => AimsInspection::STATUS_CANCELLED,
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

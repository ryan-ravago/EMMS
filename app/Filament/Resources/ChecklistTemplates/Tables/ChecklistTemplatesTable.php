<?php

namespace App\Filament\Resources\ChecklistTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecklistTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('clt_name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('checklistUsageType.cut_name')
                    ->label('Usage Type')
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('clt_interval_years')
                    ->label('Interval')
                    ->formatStateUsing(function ($record) {
                        $parts = array_filter([
                            $record->clt_interval_years  ? "{$record->clt_interval_years}y"  : null,
                            $record->clt_interval_months ? "{$record->clt_interval_months}mo" : null,
                            $record->clt_interval_weeks  ? "{$record->clt_interval_weeks}w"  : null,
                            $record->clt_interval_days   ? "{$record->clt_interval_days}d"   : null,
                        ]);

                        $interval = $parts ? implode(' ', $parts) : '—';
                        $time = $record->clt_schedule_time
                            ? \Carbon\Carbon::parse($record->clt_schedule_time)->format('h:i A')
                            : null;

                        return $time ? "{$interval} @ {$time}" : $interval;
                    })
            ])
            ->filters([
                //
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

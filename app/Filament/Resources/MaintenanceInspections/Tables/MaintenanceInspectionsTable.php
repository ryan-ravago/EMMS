<?php

namespace App\Filament\Resources\MaintenanceInspections\Tables;

use App\Models\MaintenanceInspection;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceInspectionsTable
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
                TextColumn::make('department_name')
                    ->label('Department')
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipment_name')
                    ->label('Equipment')
                    ->description(fn (MaintenanceInspection $record): ?string => $record->equipment_prc_code)
                    ->placeholder('—')
                    ->searchable(['equipment_name', 'equipment_prc_code']),
                TextColumn::make('checklist_name')
                    ->label('Checklist')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        MaintenanceInspection::STATUS_COMPLETED => 'success',
                        MaintenanceInspection::STATUS_CANCELLED => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('requested_by')
                    ->label('Requested By')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('requested_at')
                    ->label('Requested On')
                    ->dateTime('M d, Y h:i A')
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
                        MaintenanceInspection::STATUS_COMPLETED => MaintenanceInspection::STATUS_COMPLETED,
                        MaintenanceInspection::STATUS_CANCELLED => MaintenanceInspection::STATUS_CANCELLED,
                    ]),
                SelectFilter::make('department_code')
                    ->label('Department')
                    ->options(fn (): array => MaintenanceInspection::query()
                        ->whereNotNull('department_code')
                        ->distinct()
                        ->orderBy('department_name')
                        ->pluck('department_name', 'department_code')
                        ->all())
                    ->visible(fn () => auth()->user()?->hasRole('super_admin') ?? false),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

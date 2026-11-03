<?php

namespace App\Filament\Resources\RfidScannerStatuses\Tables;

use App\Models\Location;
use App\Models\RfidScannerStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RfidScannerStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('yard_id')
            ->paginated(false)
            ->columns([
                TextColumn::make('yard.name')
                    ->label('Yard')
                    ->placeholder('-'),
                TextColumn::make('scanner_name')
                    ->label('Scanner')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'IN' ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('state')
                    ->label('Status')
                    ->badge()
                    ->state(fn (RfidScannerStatus $record): string => $record->state())
                    ->formatStateUsing(fn (string $state): string => RfidScannerStatus::stateLabels()[$state] ?? $state)
                    ->color(fn (string $state): string => RfidScannerStatus::stateColor($state)),
                TextColumn::make('status_changed_at')
                    ->label('Status since')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_seen_at')
                    ->label('Last report')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('yard_id')
                    ->label('Yard')
                    ->options(fn (): array => Location::query()->orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

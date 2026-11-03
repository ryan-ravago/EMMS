<?php

namespace App\Filament\Resources\RfidScannerStatuses\Schemas;

use App\Models\RfidScannerStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RfidScannerStatusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Scanner')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('yard.name')->label('Yard')->placeholder('-'),
                        TextEntry::make('scanner_name')
                            ->label('Scanner')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'IN' ? 'success' : 'warning'),
                        TextEntry::make('state')
                            ->label('Status')
                            ->badge()
                            ->state(fn (RfidScannerStatus $record): string => $record->state())
                            ->formatStateUsing(fn (string $state): string => RfidScannerStatus::stateLabels()[$state] ?? $state)
                            ->color(fn (string $state): string => RfidScannerStatus::stateColor($state)),
                        TextEntry::make('status_changed_at')->label('Status since')->dateTime(),
                        TextEntry::make('reported_at')->label('Last status reported')->dateTime(),
                        TextEntry::make('last_report')
                            ->label('Last report received')
                            ->state(fn (RfidScannerStatus $record): string => $record->last_seen_at->diffForHumans()),
                    ]),
            ]);
    }
}

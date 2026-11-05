<?php

namespace App\Filament\Resources\AssetTagLogs\Schemas;

use App\Filament\Resources\AssetMovements\AssetMovementResource;
use App\Filament\Resources\AssetTagLogs\AssetTagLogLinks;
use App\Models\AssetMovement;
use App\Models\AssetTagLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;

class AssetTagLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Section::make('Log')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'IN' ? 'success' : 'warning'),
                        TextEntry::make('detected_at')->label('Detected')->dateTime('M d, Y h:i:s A'),
                        TextEntry::make('received_at')->label('Received')->dateTime('M d, Y h:i:s A'),
                        TextEntry::make('yard.name')->label('Yard')->placeholder('-'),
                        TextEntry::make('tag_id')
                            ->label('RFID tag')
                            ->copyable()
                            ->url(fn (AssetTagLog $record): ?string => AssetTagLogLinks::tag($record))
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        TextEntry::make('tag.asset.eqm_name')
                            ->label('Asset')
                            ->placeholder('-')
                            ->url(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record))
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        TextEntry::make('trip')
                            ->label('Trip')
                            ->state(fn (AssetTagLog $record): string => self::trip($record) ? 'Trip #'.self::trip($record)->getKey().' — '.self::trip($record)->status_label : 'Not part of a trip')
                            ->url(fn (AssetTagLog $record): ?string => self::trip($record) ? AssetMovementResource::getUrl('view', ['record' => self::trip($record)]) : null),
                    ]),
            ]);
    }

    /**
     * The trip this scan opened or closed, if any (one small lookup on the two unique log columns).
     */
    private static function trip(AssetTagLog $log): ?AssetMovement
    {
        return AssetMovement::query()
            ->where('out_log_id', $log->getKey())
            ->orWhere('in_log_id', $log->getKey())
            ->first();
    }
}

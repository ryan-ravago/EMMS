<?php

namespace App\Filament\Resources\AssetTagLogs\Schemas;

use App\Filament\Resources\AssetMovements\AssetMovementResource;
use App\Filament\Resources\AssetTagLogs\AssetTagLogLinks;
use App\Models\AssetMovement;
use App\Models\AssetTagLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssetTagLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Scan')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'IN' ? 'success' : 'warning'),
                        TextEntry::make('detected_at')->label('Detected')->dateTime('M d, Y h:i:s A'),
                        TextEntry::make('received_at')->label('Received')->dateTime('M d, Y h:i:s A'),
                        TextEntry::make('yard.name')->label('Yard')->placeholder('-'),
                    ]),
                Section::make('Tag and asset')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('tag_id')
                            ->label('RFID tag')
                            ->copyable()
                            ->url(fn (AssetTagLog $record): ?string => AssetTagLogLinks::tag($record))
                            ->color(fn (AssetTagLog $record): ?string => AssetTagLogLinks::tag($record) ? 'primary' : null),
                        TextEntry::make('tag.asset.eqm_prc_code')
                            ->label('Asset code')
                            ->placeholder('-')
                            ->url(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record))
                            ->color(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record) ? 'primary' : null),
                        TextEntry::make('tag.asset.eqm_name')
                            ->label('Asset')
                            ->placeholder('-')
                            ->url(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record))
                            ->color(fn (AssetTagLog $record): ?string => AssetTagLogLinks::asset($record) ? 'primary' : null),
                    ]),
                Section::make('Used in')
                    ->description('The equipment trip this scan became (a scan of another tag of the same asset may have been merged into it).')
                    ->schema([
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

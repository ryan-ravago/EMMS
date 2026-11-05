<?php

namespace App\Filament\Resources\RfidTags\Schemas;

use App\Filament\Resources\AssetTagLogs\AssetTagLogLinks;
use App\Models\AssetTag;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;

/**
 * The view page of a tag on the RFID Tags page. It belongs to this page alone: the nested tag page under an asset
 * has its own infolist (AssetTagInfolist), so change this one freely.
 */
class RfidTagInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel()
            ->components([
                Section::make('RFID Tag')
                    ->icon('heroicon-o-tag')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('tag_id')
                            ->label('Tag')
                            ->copyable(),
                        TextEntry::make('is_deleted')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Retired' : 'Active')
                            ->color(fn (bool $state): string => $state ? 'gray' : 'success'),
                        TextEntry::make('asset.eqm_name')
                            ->label('Asset')
                            ->placeholder('—')
                            ->url(fn (AssetTag $record): ?string => AssetTagLogLinks::assetOfTag($record))
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                        TextEntry::make('logs_count')
                            ->label('Total Logs')
                            ->state(fn (AssetTag $record): int => $record->logs()->count())
                            ->badge(),
                        TextEntry::make('assigned_by')
                            ->label('Assigned By')
                            ->state(fn (AssetTag $record): ?string => $record->assignedBy?->activityLabel())
                            ->placeholder('—'),
                        TextEntry::make('assigned_date')
                            ->label('Assigned On')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('—'),
                        TextEntry::make('scanned_by')
                            ->label('Scanned By')
                            ->state(fn (AssetTag $record): ?string => $record->scannedBy?->activityLabel())
                            ->placeholder('—'),
                        TextEntry::make('scanned_date')
                            ->label('Scanned On')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}

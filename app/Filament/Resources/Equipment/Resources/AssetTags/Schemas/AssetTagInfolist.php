<?php

namespace App\Filament\Resources\Equipment\Resources\AssetTags\Schemas;

use App\Models\AssetTag;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssetTagInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('RFID Tag')
                    ->icon('heroicon-o-tag')
                    ->columns(['default' => 1, 'md' => 2])
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
                            ->placeholder('—'),
                        TextEntry::make('asset.eqm_prc_code')
                            ->label('Asset Code')
                            ->placeholder('—'),
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
            ])->columns(1);
    }
}

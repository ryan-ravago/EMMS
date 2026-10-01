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
                Section::make()
                    ->schema([
                        TextEntry::make('tag_id')
                            ->label('Tag')
                            ->inlineLabel(),
                        TextEntry::make('asset.eqm_name')
                            ->label('Asset')
                            ->placeholder('—')
                            ->inlineLabel(),
                        TextEntry::make('asset.eqm_prc_code')
                            ->label('Asset Code')
                            ->placeholder('—')
                            ->inlineLabel(),
                        TextEntry::make('logs_count')
                            ->label('Total Logs')
                            ->state(fn(AssetTag $record): int => $record->logs()->count())
                            ->badge()
                            ->inlineLabel(),
                    ]),
            ])->columns(2);
    }
}

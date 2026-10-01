<?php

namespace App\Filament\Resources\AssetTags\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AssetTagInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('tag_id'),
                TextEntry::make('asset_parent_id')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}

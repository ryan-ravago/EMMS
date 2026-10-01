<?php

namespace App\Filament\Resources\AssetTags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tag_id')
                    ->required(),
                TextInput::make('asset_parent_id')
                    ->numeric()
                    ->default(null),
            ]);
    }
}

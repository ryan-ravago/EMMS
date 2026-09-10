<?php

namespace App\Filament\Resources\Locations\Schemas;

use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Location')
                    ->required(),
                SelectTree::make('parent_id')
                    ->label('Parent')
                    ->relationship('parent', 'name', 'parent_id')
                    ->nullable(),
            ]);
    }
}

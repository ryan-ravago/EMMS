<?php

namespace App\Filament\Resources\FuelTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FuelTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fuel_name')
                    ->required()
                    ->unique(),
            ]);
    }
}

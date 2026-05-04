<?php

namespace App\Filament\Resources\EquipmentTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EquipmentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('eqmt_name')
                    ->label('Name')
                    ->required()
                    ->unique()
                    ->maxLength(255),
            ]);
    }
}

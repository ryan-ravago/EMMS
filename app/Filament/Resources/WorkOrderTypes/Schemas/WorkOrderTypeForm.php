<?php

namespace App\Filament\Resources\WorkOrderTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkOrderTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->unique()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                TextInput::make('type')
                    ->label('Type')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }
}

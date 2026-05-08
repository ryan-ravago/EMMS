<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('dep_code')
                    ->label('Code')
                    ->required()
                    ->unique(),
                TextInput::make('dep_name')
                    ->label('Name')
                    ->required()
                    ->unique(),
                Toggle::make('is_maintenance')
                    ->label('Maintenance Department')
                    ->default(false),
            ]);
    }
}

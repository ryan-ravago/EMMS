<?php

namespace App\Filament\Resources\EquipmentTypes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Equipment Type Details')
                    ->schema([
                        TextEntry::make('eqmt_name')
                            ->label('Name'),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(4);
    }
}

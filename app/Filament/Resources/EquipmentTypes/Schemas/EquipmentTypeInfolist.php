<?php

namespace App\Filament\Resources\EquipmentTypes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EquipmentTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('eqmt_name')
                    ->label('Name'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('eqm_eqmm_id')
                    ->numeric(),
                TextEntry::make('eqm_name'),
                TextEntry::make('eqm_vin')
                    ->placeholder('-'),
                TextEntry::make('eqm_plate_num')
                    ->placeholder('-'),
                TextEntry::make('eqm_prc_code')
                    ->placeholder('-'),
                IconEntry::make('eqm_is_active')
                    ->boolean(),
                TextEntry::make('eqm_updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

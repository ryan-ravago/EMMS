<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('SAP Information')
                    ->description('Data synced from SAP. These fields are read-only.')
                    ->icon('heroicon-o-server')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('eqm_prc_code')
                            ->label('PRC Code'),
                        TextEntry::make('eqm_name')
                            ->label('Equipment Name')
                            ->columnSpanFull(),
                        IconEntry::make('eqm_is_active')
                            ->label('Active')
                            ->boolean(),
                    ]),

                Section::make('Equipment Details')
                    ->description('Additional information you can fill in manually.')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('model.eqmm_name')
                            ->label('Model')
                            ->columnSpanFull()
                            ->placeholder('—'),
                        TextEntry::make('eqm_vin')
                            ->label('Vehicle Identification Number')
                            ->placeholder('—'),
                        TextEntry::make('eqm_plate_num')
                            ->label('Plate Number')
                            ->placeholder('—'),
                        TextEntry::make('eqm_serial_num')
                            ->label('Serial #')
                            ->placeholder('—'),
                        TextEntry::make('eqm_engine')
                            ->label('Engine')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}

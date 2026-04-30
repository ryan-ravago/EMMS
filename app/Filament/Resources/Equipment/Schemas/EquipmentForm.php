<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentForm
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
                        TextInput::make('eqm_prc_code')
                            ->label('PRC Code')
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('eqm_name')
                            ->label('Equipment Name')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                        Toggle::make('eqm_is_active')
                            ->label('Active')
                            ->disabled()
                            ->dehydrated(),
                    ]),

                Section::make('Equipment Details')
                    ->description('Additional information you can fill in manually.')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(2)
                    ->schema([
                        Select::make('eqm_eqmm_id')
                            ->label('Model')
                            ->relationship('model', 'eqmm_name')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        TextInput::make('eqm_vin')
                            ->label('Vehicle Identification Number')
                            ->placeholder('e.g. 1HGBH41JXMN109186')
                            ->maxLength(17),
                        TextInput::make('eqm_plate_num')
                            ->label('Plate Number')
                            ->placeholder('e.g. ABC 1234'),
                    ]),
            ]);
    }
}

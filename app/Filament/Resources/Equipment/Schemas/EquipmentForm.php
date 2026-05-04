<?php

namespace App\Filament\Resources\Equipment\Schemas;

use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
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
                            ->createOptionForm([
                                TextInput::make('eqmm_name')
                                    ->label('Name')
                                    ->required()
                                    ->unique(),
                                SelectTree::make('eqmm_eqmc_id')
                                    ->label('Category')
                                    ->relationship('category', 'eqmc_name', 'eqmc_parent_id'),
                                Select::make('eqmm_brand_id')
                                    ->label('Brand')
                                    ->relationship('brand', 'eqmb_name')
                                    ->native(false),
                                Select::make('eqmm_fuel_type')
                                    ->label('Fuel Type')
                                    ->relationship('fuel_type', 'fuel_name')
                                    ->native(false),
                            ])
                            ->createOptionAction(function (Action $action) {
                                return $action
                                    ->modalHeading('Add New Model')
                                    ->modalWidth('md'); // xs, sm, md, lg, xl, 2xl
                            })
                            ->columnSpanFull(),
                        TextInput::make('eqm_vin')
                            ->label('Vehicle Identification Number')
                            ->placeholder('e.g. 1HGBH41JXMN109186')
                            ->maxLength(17),
                        TextInput::make('eqm_plate_num')
                            ->label('Plate Number')
                            ->placeholder('e.g. ABC 1234'),
                        TextInput::make('eqm_serial_num')
                            ->label('Serial #')
                            ->placeholder('e.g. SN-000123')
                            ->unique()
                            ->maxLength(255),
                        TextInput::make('eqm_engine')
                            ->label('Engine')
                            ->placeholder('e.g. 4JJ1')
                            ->maxLength(255),
                    ]),
            ]);
    }
}

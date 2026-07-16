<?php

namespace App\Filament\Resources\Models\Resources\Equipment\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make('SAP Information')
                            ->description('Data synced from SAP. These fields are read-only.')
                            ->icon('heroicon-o-server')
                            ->schema([
                                TextInput::make('eqm_prc_code')
                                    ->label('Equipment Code')
                                    ->disabled()
                                    ->dehydrated(), // Occupies half-width on the first row,
                                TextInput::make('eqm_name')
                                    ->label('Equipment Name')
                                    ->disabled()
                                    ->dehydrated(),
                                Toggle::make('eqm_is_active')
                                    ->label('Active')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ]),
                    ]),

                Grid::make(3)
                    ->schema([
                        Section::make('Equipment Details')
                            ->description('Additional information you can fill in manually.')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Select::make('eqm_eqmm_id')
                                    ->label('Model')
                                    ->relationship('equipmentModel', 'eqmm_name')
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('eqmm_name')
                                            ->label('Model Name')
                                            ->required()
                                            ->unique(),
                                        // Select::make('eqmm_brand_id')
                                        //     ->label('Brand')
                                        //     ->relationship('brand', 'eqmb_name')
                                        //     ->native(false)
                                        //     ->createOptionForm([
                                        //         TextInput::make('eqmb_name')
                                        //             ->label('Brand Name')
                                        //             ->required()
                                        //             ->unique(),
                                        //     ])
                                        //     ->createOptionAction(function (Action $action) {
                                        //         return $action
                                        //             ->modalHeading('Add New Equipment Brand')
                                        //             ->modalWidth('md'); // xs, sm, md, lg, xl, 2xl
                                        //     }),
                                        // Select::make('eqm_eqmt_id')
                                        //     ->label('Type')
                                        //     ->relationship('type', 'eqmt_name')
                                        //     ->native(false)
                                        //     ->required()
                                        //     ->createOptionForm([
                                        //         TextInput::make('eqmt_name')
                                        //             ->label('Type Name')
                                        //             ->required()
                                        //             ->unique()
                                        //             ->maxLength(255),
                                        //     ])
                                        //     ->createOptionAction(function (Action $action) {
                                        //         return $action
                                        //             ->modalHeading('Add New Equipment Type')
                                        //             ->modalWidth('md');
                                        //     }),
                                        // Select::make('eqmm_fuel_type')
                                        //     ->label('Fuel Type')
                                        //     ->relationship('fuel_type', 'fuel_name')
                                        //     ->native(false),
                                    ])
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Add New Equipment Model')
                                            ->modalWidth('md'); // xs, sm, md, lg, xl, 2xl
                                    })
                                    ->columnSpanFull(),

                                Select::make('eqm_brand_id')
                                    ->label('Brand')
                                    ->relationship('brand', 'eqmb_name')
                                    ->native(false)
                                    ->createOptionForm([
                                        TextInput::make('eqmb_name')
                                            ->label('Brand Name')
                                            ->required()
                                            ->unique(),
                                    ])
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Add New Equipment Brand')
                                            ->modalWidth('md'); // xs, sm, md, lg, xl, 2xl
                                    }),

                                Select::make('eqm_eqmt_id')
                                    ->label('Type')
                                    ->relationship('type', 'eqmt_name')
                                    ->native(false)
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('eqmt_name')
                                            ->label('Type Name')
                                            ->required()
                                            ->unique()
                                            ->maxLength(255),
                                    ])
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Add New Equipment Type')
                                            ->modalWidth('md');
                                    }),

                                // TextInput::make('eqm_vin')
                                //     ->label('Vehicle Identification Number')
                                //     ->placeholder('e.g. 1HGBH41JXMN109186')
                                //     ->maxLength(17)
                                //     ->columnSpanFull(),

                                TextInput::make('eqm_plate_num')
                                    ->label('Plate #')
                                    ->placeholder('e.g. ABC 1234')
                                    ->columnSpanFull(),

                                // TextInput::make('eqm_serial_num')
                                //     ->label('Serial #')
                                //     ->placeholder('e.g. SN-000123')
                                //     ->unique()
                                //     ->maxLength(255)
                                //     ->columnSpanFull(),

                                // TextInput::make('eqm_engine')
                                //     ->label('Engine')
                                //     ->placeholder('e.g. 4JJ1')
                                //     ->maxLength(255)
                                //     ->columnSpanFull(),

                                TextInput::make('eqm_chassis_no')
                                    ->label('Chassis #')
                                    ->columnStart(1)
                                    ->extraAttributes(['class' => 'max-w-lg'])
                                    ->maxLength(255),

                                DatePicker::make('eqm_date_purchased')
                                    ->label('Date Purchased')
                                    ->columnStart(1)
                                    ->extraAttributes(['class' => 'max-w-lg'])
                                    ->native(false)
                                    ->maxDate(today()),
                            ]),
                    ]),
            ])->columns(1);
    }
}

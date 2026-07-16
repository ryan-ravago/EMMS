<?php

namespace App\Filament\Resources\Models\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Master Container - stacked vertically
                Grid::make(3)
                    ->schema([

                        // --- SECTION 1: CORE INFORMATION ---
                        Section::make('General Information')
                            ->description('Basic identification details for this equipment model.')
                            ->icon('heroicon-o-identification')
                            ->schema([

                                TextInput::make('eqmm_name')
                                    ->label('Model Name')
                                    ->placeholder('e.g. Caterpillar 320D')
                                    ->required()
                                    ->unique(),

                                // Select::make('eqmm_brand_id')
                                //     ->label('Brand')
                                //     ->relationship('brand', 'eqmb_name')
                                //     ->native(false)
                                //     ->createOptionForm([
                                //         TextInput::make('eqmb_name')
                                //             ->label('Name')
                                //             ->required()
                                //             ->unique(),
                                //     ])
                                //     ->createOptionAction(fn(Action $action) => $action->modalHeading('Add New Brand')->modalWidth('md')),

                                // Select::make('eqmm_eqmt_id')
                                //     ->label('Equipment Type')
                                //     ->relationship('type', 'eqmt_name')
                                //     ->native(false)
                                //     ->searchable()
                                //     ->preload()
                                //     ->exists('equipment_types', 'eqmt_id')
                                //     ->createOptionForm([
                                //         TextInput::make('eqmt_name')
                                //             ->label('Name')
                                //             ->required()
                                //             ->unique()
                                //             ->maxLength(255),
                                //     ])
                                //     ->createOptionAction(fn(Action $action) => $action->modalHeading('Add New Equipment Type')->modalWidth('md'))
                                //     ->editOptionForm([
                                //         TextInput::make('eqmt_name')
                                //             ->label('Name')
                                //             ->required()
                                //             ->unique()
                                //             ->maxLength(255),
                                //     ])
                            ]),

                        // --- SECTION 2: TECHNICAL SPECIFICATIONS ---
                        Section::make('Technical Specifications')
                            ->description('Physical dimensions and operational capacity parameters.')
                            ->icon('heroicon-o-adjustments-vertical')
                            ->columns(2)
                            ->columnStart(1)
                            ->schema([

                                TextInput::make('eqmm_max_capacity_tons')
                                    ->label('Max Capacity')
                                    ->suffix('tons') // Moves units out of label into input box
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->columnSpan(1), // Small-Medium field width

                                TextInput::make('eqmm_max_reach_meters')
                                    ->label('Max Reach')
                                    ->suffix('meters') // Moves units out of label into input box
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->columnSpan(1) // Small-Medium field width
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}

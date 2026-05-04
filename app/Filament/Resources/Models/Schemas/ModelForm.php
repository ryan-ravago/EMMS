<?php

namespace App\Filament\Resources\Models\Schemas;

use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('eqmm_name')
                    ->label('Name')
                    ->required()
                    ->unique(),
                Select::make('eqmm_eqmt_id')
                    ->label('Equipment Type')
                    ->relationship('type', 'eqmt_name')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->exists('equipment_types', 'eqmt_id'),
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
                TextInput::make('eqmm_max_capacity_tons')
                    ->label('Max Capacity (tons)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                TextInput::make('eqmm_max_reach_meters')
                    ->label('Max Reach (meters)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
            ]);
    }
}

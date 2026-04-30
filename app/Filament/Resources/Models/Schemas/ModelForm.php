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
            ]);
    }
}

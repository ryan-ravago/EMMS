<?php

namespace App\Filament\Resources\Categories\Schemas;

use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('eqmc_name')
                    ->label('Namdde')
                    ->required(),
                // ->unique(table: 'equipment_categories', column: 'eqmc_name', ignoreRecord: true),
                SelectTree::make('eqmc_parent_id')
                    ->label('Parent')
                    ->relationship('parent', 'eqmc_name', 'eqmc_parent_id')
                    ->nullable(),
            ]);
    }
}

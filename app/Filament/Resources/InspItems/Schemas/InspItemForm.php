<?php

namespace App\Filament\Resources\InspItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InspItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('inspi_id')
                    ->required()
                    ->maxLength(255),
                Select::make('inspi_insp_id')
                    ->relationship('inspection', 'insp_no')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('inspi_task')
                    ->maxLength(255)
                    ->default(null),
                Select::make('inspi_result')
                    ->options([
                        'Passed' => 'Passed',
                        'Failed' => 'Failed',
                        'N/A' => 'N/A',
                    ])
                    ->default(null),
                Textarea::make('inspi_remarks')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}

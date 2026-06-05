<?php

namespace App\Filament\Resources\InspectionItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InspectionItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('insi_ins_id')
                    ->required()
                    ->numeric(),
                TextInput::make('insi_task_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('insi_status_id')
                    ->required(),
                TextInput::make('insi_result')
                    ->default(null),
                TextInput::make('insi_cli_name_for_record')
                    ->required(),
                Textarea::make('insi_remarks')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}

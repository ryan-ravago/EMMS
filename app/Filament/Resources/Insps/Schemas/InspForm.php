<?php

namespace App\Filament\Resources\Insps\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InspForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('insp_id')
                    ->required()
                    ->maxLength(255),
                TextInput::make('insp_no')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('insp_dep_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('insp_eqm_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('is_submitted')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('checklist_template_name')
                    ->maxLength(255)
                    ->default(null),
                Textarea::make('checklist_temp_items')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('insp_remarks')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('insp_by')
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('insp_submitted_by')
                    ->maxLength(255)
                    ->default(null),
                DateTimePicker::make('insp_submitted_at'),
            ]);
    }
}

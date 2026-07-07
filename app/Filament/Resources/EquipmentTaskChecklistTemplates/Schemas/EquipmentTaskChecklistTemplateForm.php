<?php

namespace App\Filament\Resources\EquipmentTaskChecklistTemplates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EquipmentTaskChecklistTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('etct_dep_id')
                    ->required()
                    ->numeric(),
                TextInput::make('etct_eqmt_id')
                    ->required()
                    ->numeric(),
                TextInput::make('etct_task_id')
                    ->required()
                    ->numeric(),
                TextInput::make('etct_created_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('etct_created_at')
                    ->required(),
            ]);
    }
}

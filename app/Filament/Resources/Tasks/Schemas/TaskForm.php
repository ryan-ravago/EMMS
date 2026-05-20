<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('task_name')
                    ->required(),
                TextInput::make('task_dep_id')
                    ->required()
                    ->numeric(),
                TextInput::make('task_tut_id')
                    ->required()
                    ->numeric(),
                TextInput::make('task_created_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('task_created_at')
                    ->required(),
                TextInput::make('task_last_updated_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('task_last_updated_at')
                    ->required(),
            ]);
    }
}

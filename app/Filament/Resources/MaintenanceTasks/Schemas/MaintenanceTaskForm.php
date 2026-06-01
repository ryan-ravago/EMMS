<?php

namespace App\Filament\Resources\MaintenanceTasks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MaintenanceTaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mt_eqm_id')
                    ->required()
                    ->numeric(),
                TextInput::make('mt_eqm_log')
                    ->default(null),
                TextInput::make('mt_dep_id')
                    ->required()
                    ->numeric(),
                TextInput::make('mt_task_id')
                    ->required()
                    ->numeric(),
                TextInput::make('mt_task_log')
                    ->default(null),
                TextInput::make('mt_status_id')
                    ->required(),
                DateTimePicker::make('mt_due_dt'),
                Textarea::make('mt_remarks')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('mt_scheduled_dt')
                    ->required(),
                DateTimePicker::make('mt_closed_dt'),
                TextInput::make('mt_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('mt_dt')
                    ->required(),
            ]);
    }
}

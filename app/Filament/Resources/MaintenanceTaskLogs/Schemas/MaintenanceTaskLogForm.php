<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MaintenanceTaskLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mtl_mt_id')
                    ->required()
                    ->numeric(),
                TextInput::make('mtl_status_id')
                    ->required(),
                DateTimePicker::make('mtl_due_dt'),
                TextInput::make('mtl_last_act_made')
                    ->default(null),
                Textarea::make('mtl_remarks')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('mtl_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('mtl_dt')
                    ->required(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('task_name'),
                TextEntry::make('task_dep_id')
                    ->numeric(),
                TextEntry::make('task_tut_id')
                    ->numeric(),
                TextEntry::make('task_created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('task_created_at')
                    ->dateTime(),
                TextEntry::make('task_last_updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('task_last_updated_at')
                    ->dateTime(),
            ]);
    }
}

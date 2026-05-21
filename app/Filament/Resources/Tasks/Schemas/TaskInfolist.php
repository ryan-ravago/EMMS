<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task Details')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('task_name')
                            ->label('Task Name')
                            ->columnSpanFull(),
                        TextEntry::make('department.dep_name')
                            ->label('Department'),
                        TextEntry::make('taskUsageType.tut_name')
                            ->label('Usage Type'),
                    ]),

                Section::make('Audit Info')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('createdBy.user_fname')
                            ->label('Created By')
                            ->placeholder('-'),
                        TextEntry::make('task_created_at')
                            ->label('Created At')
                            ->dateTime('M d, Y | h:i A'),
                        TextEntry::make('lastUpdatedBy.user_fname')
                            ->label('Last Updated By')
                            ->placeholder('-'),
                        TextEntry::make('task_last_updated_at')
                            ->label('Last Updated At')
                            ->dateTime('M d, Y | h:i A')
                    ]),
            ]);
    }
}

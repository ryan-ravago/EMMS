<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceTaskLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Log Details')
                    ->description('General information about this maintenance log entry.')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        TextEntry::make('status.status_title')
                            ->label('Status')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('loggedBy.name')
                            ->label('Logged By')
                            ->icon('heroicon-m-user')
                            ->placeholder('System / Unknown'),

                        TextEntry::make('mtl_dt')
                            ->label('Log Date')
                            ->dateTime()
                            ->icon('heroicon-m-calendar')
                            ->placeholder('-'),

                        TextEntry::make('mtl_due_dt')
                            ->label('Due Date')
                            ->dateTime()
                            ->icon('heroicon-m-clock')
                            ->placeholder('Not specified'),
                    ])
                    ->columns(['sm' => 1, 'md' => 2]),

                Section::make('Action & Remarks')
                    ->icon('heroicon-m-chat-bubble-bottom-center-text')
                    ->schema([
                        TextEntry::make('mtl_last_act_made')
                            ->label('Last Action Made')
                            ->placeholder('No specific action recorded.')
                            ->columnSpanFull(),

                        TextEntry::make('mtl_remarks')
                            ->label('Remarks')
                            ->placeholder('No remarks provided.')
                            ->columnSpanFull(),
                    ])
            ]);
    }
}

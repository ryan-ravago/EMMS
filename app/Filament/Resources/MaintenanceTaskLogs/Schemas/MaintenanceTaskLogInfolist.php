<?php

namespace App\Filament\Resources\MaintenanceTaskLogs\Schemas;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\MaintenanceTaskLog;
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
                            ->color(fn (MaintenanceTaskLog $record): string => $record->status->status_color)
                            ->icon(fn (MaintenanceTaskLog $record): string => $record->status->status_icon)
                            ->placeholder('-'),

                        TextEntry::make('logBy.full_name')
                            ->label('Logged By')
                            ->icon('heroicon-m-user')
                            ->placeholder('System / Unknown'),

                        TextEntry::make('mtl_dt')
                            ->label('Log Date')
                            ->dateTime('M j, Y h:i A')
                            ->icon('heroicon-m-calendar')
                            ->placeholder('-'),

                        TextEntry::make('mtl_due_dt')
                            ->label('Due Date')
                            ->dateTime('M d, Y | h:i A')
                            ->icon('heroicon-m-clock')
                            ->placeholder('Not specified'),

                        TextEntry::make('workOrder.wo_no')
                            ->label('WO #')
                            ->color('info')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition('after')
                            ->url(
                                fn ($record) => $record->workOrder
                                    ? WorkOrderResource::getUrl('view', ['record' => $record->workOrder->wo_id])
                                    : null
                            ),
                    ])
                    ->columns(['sm' => 1, 'md' => 2]),

                Section::make('Action & Remarks')
                    ->icon('heroicon-m-chat-bubble-bottom-center-text')
                    ->schema([
                        TextEntry::make('action.a_past_tense')
                            ->label('Last Action Made')
                            ->placeholder('No specific action recorded.')
                            ->columnSpanFull(),

                        TextEntry::make('mtl_remarks')
                            ->label('Remarks')
                            ->placeholder('No remarks provided.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

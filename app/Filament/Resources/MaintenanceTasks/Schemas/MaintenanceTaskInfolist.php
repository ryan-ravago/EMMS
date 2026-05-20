<?php

namespace App\Filament\Resources\MaintenanceTasks\Schemas;

use App\Models\MaintenanceTask;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceTaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('equipmentUnit.eqm_name')
                            ->label('Equipment'),

                        TextEntry::make('department.dep_name')
                            ->label('Department')
                            ->visible(fn() => once(fn() => auth()->user()->hasRole('super_admin'))),

                        TextEntry::make('mt_task_log')
                            ->label('Task')
                            ->placeholder('-'),

                        TextEntry::make('status.status_title')
                            ->label('Status')
                            ->badge()
                            ->color(fn(MaintenanceTask $record): string => $record->status?->status_color)
                            ->icon(fn(MaintenanceTask $record): string => $record->status->status_icon),
                        TextEntry::make('mt_due_dt')
                            ->label('Due Date')
                            ->dateTime('M j, Y h:i A')
                            ->placeholder('-')
                            ->color(
                                fn($record) =>
                                $record->mt_closed_dt === null && $record->mt_due_dt < now()
                                    ? 'danger'
                                    : null
                            ),

                        TextEntry::make('mt_remarks')
                            ->label('Remarks')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Schedule & Closure')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('mt_scheduled_dt')
                            ->label('Scheduled Date')
                            ->dateTime('M j, Y h:i A'),

                        TextEntry::make('mt_closed_dt')
                            ->label('Closed Date')
                            ->dateTime('M j, Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('createdBy.user_fname')
                            ->label('Created By')
                            ->formatStateUsing(fn($record) => $record?->createdBy
                                ? "{$record->createdBy->user_fname} {$record->createdBy->user_lname}"
                                : '-')
                            ->placeholder('-'),

                        TextEntry::make('mt_dt')
                            ->label('Created At')
                            ->dateTime('M j, Y h:i A'),
                    ]),
            ]);
    }
}

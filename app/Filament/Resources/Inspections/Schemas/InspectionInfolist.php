<?php

namespace App\Filament\Resources\Inspections\Schemas;

use App\Models\InspectionItem;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InspectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inspection Details')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('department.dep_name')
                            ->label('Department')
                            ->visible(fn() => auth()->user()->hasRole('super_admin')),

                        TextEntry::make('equipment.eqm_name')
                            ->label('Equipment'),

                        TextEntry::make('conductedBy.user_fname')
                            ->label('Inspected By')
                            ->formatStateUsing(fn($record) => "{$record->conductedBy->user_fname} {$record->conductedBy->user_lname}"),

                        TextEntry::make('ins_dt')
                            ->label('Inspection Date & Time')
                            ->dateTime('M d, Y | h:i A'),

                        TextEntry::make('submittedBy.user_fname')
                            ->label('Submitted By')
                            ->formatStateUsing(fn($record) => $record->submittedBy
                                ? "{$record->submittedBy->user_fname} {$record->submittedBy->user_lname}"
                                : '—'),

                        TextEntry::make('ins_submitted_dt')
                            ->label('Submitted At')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('—'),
                    ]),

                Section::make('Inspection Tasks')
                    ->icon('heroicon-o-list-bullet')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('inspectionItems')
                            ->hiddenLabel()
                            ->columns(2)
                            ->grid(3)
                            ->schema([
                                TextEntry::make('insi_cli_name_for_record')
                                    ->label('Task')
                                    ->columnSpan(2),

                                TextEntry::make('insi_result')
                                    ->label('Result')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'P' => 'success',
                                        'F' => 'danger',
                                        'N' => 'gray',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'P' => 'Passed',
                                        'F' => 'Failed',
                                        'N' => 'N/A',
                                        default => '—',
                                    })
                                    ->columnSpan(1),

                                TextEntry::make('status.status_title')
                                    ->label('Status')
                                    ->badge()
                                    ->columnSpan(1)
                                    ->color(fn(InspectionItem $record) => $record->status->status_color)
                                    ->icon(fn(InspectionItem $record) => $record->status->status_icon),

                                TextEntry::make('insi_remarks')
                                    ->label('Remarks')
                                    ->placeholder('—')
                                    ->columnSpan(2),

                                TextEntry::make('view_link')
                                    ->hiddenLabel()
                                    ->default('View details →')
                                    ->url(fn($record) => url("/inspection-items/{$record->insi_id}"))
                                    ->columnSpan(2)
                                    ->extraAttributes([
                                        'class' => 'text-right fi-link text-primary-600 hover:text-primary-500 hover:underline font-medium cursor-pointer',
                                    ]),
                            ]),
                    ]),
            ]);
    }
}

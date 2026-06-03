<?php

namespace App\Filament\Resources\Inspections\Schemas;

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

                Section::make('Inspection Items')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        RepeatableEntry::make('inspectionItems')
                            ->label('')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('insi_cli_name_for_record')
                                    ->label('Task')
                                    ->columnSpan(1),

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

                                TextEntry::make('insi_remarks')
                                    ->label('Remarks')
                                    ->placeholder('—')
                                    ->columnSpan(2),
                            ]),
                    ]),
            ]);
    }
}

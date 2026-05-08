<?php

namespace App\Filament\Resources\ChecklistTemplates\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ChecklistTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->description('Basic details of the checklist template.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('clt_name')
                            ->label('Checklist Name')
                            ->columnSpanFull(),
                        TextEntry::make('checklistUsageType.cut_name')
                            ->label('Usage Type'),
                        TextEntry::make('department.dep_name')
                            ->label('Department'),
                        IconEntry::make('clt_is_active')
                            ->label('Active')
                            ->boolean(),
                    ]),

                Section::make('Schedule & Interval')
                    ->description('Recurrence interval and scheduled run time.')
                    ->columns(4)
                    ->hidden(fn($record) => $record->clt_cut_id == 1)
                    ->schema([
                        TextEntry::make('clt_interval_years')
                            ->label('Years')
                            ->placeholder('-'),
                        TextEntry::make('clt_interval_months')
                            ->label('Months')
                            ->placeholder('-'),
                        TextEntry::make('clt_interval_weeks')
                            ->label('Weeks')
                            ->placeholder('-'),
                        TextEntry::make('clt_interval_days')
                            ->label('Days')
                            ->placeholder('-'),
                        TextEntry::make('clt_schedule_time')
                            ->label('Schedule Time')
                            ->time()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Checklist Items')
                    ->description(fn(Get $get) => $get('clt_cut_id') == 2
                        ? 'Items included in this scheduled checklist.'
                        : 'Items included in this pre-operational checklist.')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('checklistItems')
                            ->schema([
                                TextEntry::make('cli_name')
                                    ->label('Item Name'),
                                TextEntry::make('cli_sort_order')
                                    ->label('Order'),
                            ])
                            ->columns(2)
                            ->grid(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Audit')
                    ->description('Record creation and last modification timestamps.')
                    ->columns(2)
                    // ->collapsed()
                    ->schema([
                        TextEntry::make('clt_created_dt')
                            ->label('Created At')
                            ->dateTime('M d, Y h:i A'),
                        TextEntry::make('clt_last_updated_dt')
                            ->label('Last Updated At')
                            ->dateTime('M d, Y h:i A'),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\InspectionItems\Schemas;

use App\Filament\Resources\Inspections\InspectionResource;
use App\Models\InspectionItem;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;

class InspectionItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inspection Item Details')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('insi_no')
                            ->label('Item #')
                            ->weight('bold'),
                        TextEntry::make('inspection.ins_no')
                            ->label('Inspection #')
                            ->url(fn (InspectionItem $record) => InspectionResource::getUrl('view', ['record' => $record->insi_ins_id]))
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),

                        TextEntry::make('inspection.equipment.eqm_name')
                            ->label('Equipment'),

                        TextEntry::make('insi_cli_name_for_record')
                            ->label('Task'),

                        TextEntry::make('insi_result')
                            ->label('Result')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'P' => 'success',
                                'F' => 'danger',
                                'N' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'P' => 'Passed',
                                'F' => 'Failed',
                                'N' => 'N/A',
                                default => '—',
                            }),

                        TextEntry::make('status.status_title')
                            ->label('Status')
                            ->badge()
                            ->color(fn (InspectionItem $record) => $record->status->status_color)
                            ->icon(fn (InspectionItem $record) => $record->status->status_icon),

                        TextEntry::make('insi_closed_dt')
                            ->label('Closed At')
                            ->placeholder('-')
                            ->dateTime('M d, Y | h:i A'),

                        TextEntry::make('insi_remarks')
                            ->label('Remarks')
                            ->placeholder('—')
                            ->columnSpanFull(),

                    ]),
            ]);
    }
}

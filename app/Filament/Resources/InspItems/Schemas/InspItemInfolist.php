<?php

namespace App\Filament\Resources\InspItems\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InspItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inspection Item Details')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('inspi_id')
                            ->label('Item ID')
                            ->weight('bold'),
                        TextEntry::make('inspection.insp_no')
                            ->label('Inspection #')
                            ->placeholder('—'),
                        TextEntry::make('inspi_task')
                            ->label('Task')
                            ->placeholder('—'),
                        TextEntry::make('inspi_result')
                            ->label('Result')
                            ->badge()
                            ->placeholder('—'),
                        TextEntry::make('inspi_remarks')
                            ->label('Remarks')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

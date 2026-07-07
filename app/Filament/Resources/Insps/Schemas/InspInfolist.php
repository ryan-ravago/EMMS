<?php

namespace App\Filament\Resources\Insps\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InspInfolist
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
                        TextEntry::make('insp_id')
                            ->label('Inspection ID')
                            ->weight('bold'),
                        TextEntry::make('insp_no')
                            ->label('Inspection #')
                            ->placeholder('—'),
                        TextEntry::make('insp_dep_id')
                            ->label('Department')
                            ->placeholder('—'),
                        TextEntry::make('insp_eqm_id')
                            ->label('Equipment ID')
                            ->placeholder('—'),
                        TextEntry::make('is_submitted')
                            ->label('Submitted Status')
                            ->placeholder('—')
                            ->badge(),
                        TextEntry::make('insp_submitted_at')
                            ->label('Submitted At')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('—'),
                        TextEntry::make('checklist_template_name')
                            ->label('Checklist Template')
                            ->placeholder('—')
                            ->columnSpan(2),
                        TextEntry::make('checklist_temp_items')
                            ->label('Checklist Items')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('insp_remarks')
                            ->label('Remarks')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

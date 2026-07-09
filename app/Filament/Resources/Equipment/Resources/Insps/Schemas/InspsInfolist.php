<?php

namespace App\Filament\Resources\Equipment\Resources\Insps\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InspsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inspection Details')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('insp_no')
                            ->label('Inspection #')
                            ->placeholder('—'),
                        TextEntry::make('insp_dep_id')
                            ->label('Department')
                            ->visible(fn() => auth()->user()->hasRole(['super_admin']))
                            ->placeholder('—'),
                        TextEntry::make('equipment.eqm_name')
                            ->label('Equipment Unit')
                            ->placeholder('—'),
                        TextEntry::make('is_submitted')
                            ->label('Submitted Status')
                            ->placeholder('—')
                            ->badge(),
                        TextEntry::make('checklist_template_name')
                            ->label('Checklist Template')
                            ->placeholder('—'),
                        TextEntry::make('insp_submitted_at')
                            ->label('Submitted On')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('—'),
                        TextEntry::make('insp_remarks')
                            ->label('Remarks')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

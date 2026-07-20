<?php

namespace App\Filament\Resources\Insps\Schemas;

use App\Filament\Resources\Equipment\EquipmentResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;

class InspInfolist
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
                            ->url(fn($record) => EquipmentResource::getUrl('view', ['record' => $record->insp_eqm_id]))
                            ->placeholder('—')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
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

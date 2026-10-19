<?php

namespace App\Filament\Resources\AimsInspections\Schemas;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\AimsInspection;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;

class AimsInspectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inspection Details')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('inspection_no')
                            ->label('Inspection #'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                AimsInspection::STATUS_COMPLETED => 'success',
                                AimsInspection::STATUS_CANCELLED => 'gray',
                                default => 'warning',
                            }),
                        TextEntry::make('equipment_name')
                            ->label('Equipment')
                            ->formatStateUsing(fn (AimsInspection $record): string => trim("{$record->equipment_name} ({$record->equipment_prc_code})", ' ()'))
                            ->url(fn (AimsInspection $record): ?string => $record->equipment ? EquipmentResource::getUrl('view', ['record' => $record->equipment]) : null)
                            ->icon(fn (AimsInspection $record): ?string => $record->equipment ? 'heroicon-o-arrow-top-right-on-square' : null)
                            ->iconPosition(IconPosition::After)
                            ->placeholder('—'),
                        TextEntry::make('checklist_name')
                            ->label('Checklist')
                            ->placeholder('—'),
                        TextEntry::make('lessee')->placeholder('—'),
                        TextEntry::make('jobsite')->placeholder('—'),
                        TextEntry::make('operator')->placeholder('—'),
                        TextEntry::make('day_duration')
                            ->label('Duration (days)')
                            ->placeholder('—'),
                    ]),

                Section::make('Inspection Result')
                    ->icon('heroicon-o-check-badge')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('inspected_by')
                            ->label('Inspected By')
                            ->placeholder('—'),
                        TextEntry::make('inspected_at')
                            ->label('Inspected On')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('—'),
                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->placeholder('—'),
                        TextEntry::make('completion_remarks')
                            ->label('Completion Remarks')
                            ->placeholder('—'),
                        TextEntry::make('cancel_remarks')
                            ->label('Cancel Reason')
                            ->placeholder('—')
                            ->visible(fn (AimsInspection $record): bool => $record->status === AimsInspection::STATUS_CANCELLED),
                        TextEntry::make('requested_by')
                            ->label('Requested By')
                            ->placeholder('—'),
                        TextEntry::make('requested_at')
                            ->label('Requested On')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('—'),
                        TextEntry::make('closed_at')
                            ->label('Closed On')
                            ->dateTime('M d, Y | h:i A')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\MaintenanceInspections\Schemas;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\MaintenanceInspection;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;

class MaintenanceInspectionInfolist
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
                                MaintenanceInspection::STATUS_COMPLETED => 'success',
                                MaintenanceInspection::STATUS_CANCELLED => 'gray',
                                default => 'warning',
                            }),
                        TextEntry::make('department_name')
                            ->label('Department')
                            ->placeholder('—'),
                        TextEntry::make('equipment_name')
                            ->label('Equipment')
                            ->formatStateUsing(fn (MaintenanceInspection $record): string => trim("{$record->equipment_name} ({$record->equipment_prc_code})", ' ()'))
                            ->url(fn (MaintenanceInspection $record): ?string => $record->equipment ? EquipmentResource::getUrl('view', ['record' => $record->equipment]) : null)
                            ->icon(fn (MaintenanceInspection $record): ?string => $record->equipment ? 'heroicon-o-arrow-top-right-on-square' : null)
                            ->iconPosition(IconPosition::After)
                            ->placeholder('—'),
                        TextEntry::make('checklist_name')
                            ->label('Checklist')
                            ->placeholder('—'),
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
                        TextEntry::make('cancel_remarks')
                            ->label('Cancel Reason')
                            ->placeholder('—')
                            ->visible(fn (MaintenanceInspection $record): bool => $record->status === MaintenanceInspection::STATUS_CANCELLED),
                    ]),

                Section::make('Approval & Submission')
                    ->icon('heroicon-o-check-badge')
                    ->inlineLabel()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('asset_admin_manager_note')->label('Request Note')->placeholder('—'),
                        TextEntry::make('manager_action')->label('Manager Decision')->placeholder('—'),
                        TextEntry::make('manager_action_by')->label('Decided By')->placeholder('—'),
                        TextEntry::make('manager_remarks')->label('Manager Remarks')->placeholder('—'),
                        TextEntry::make('technicians')->label('Technicians')->placeholder('—'),
                        TextEntry::make('tech_action')->label('Technician Action')->placeholder('—'),
                        TextEntry::make('tech_action_by')->label('Submitted By')->placeholder('—'),
                        TextEntry::make('tech_remarks')->label('Technician Remarks')->placeholder('—'),
                        TextEntry::make('verifiers')->label('Verifiers')->placeholder('—'),
                        TextEntry::make('verifier_action')->label('Verifier Decision')->placeholder('—'),
                        TextEntry::make('verifier_action_by')->label('Verified By')->placeholder('—'),
                        TextEntry::make('verifier_remarks')->label('Verifier Remarks')->placeholder('—'),
                    ]),
            ]);
    }
}

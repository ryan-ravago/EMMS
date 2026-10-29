<?php

namespace App\Filament\Resources\AssetMovements\Schemas;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\Equipment\Resources\AssetTags\AssetTagResource;
use App\Models\AssetMovement;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AssetMovementInfolist
{
    /**
     * The equipment's own page, or null (no link) when the viewer may not open it.
     */
    private static function equipmentUrl(AssetMovement $record): ?string
    {
        if ($record->asset === null || ! Auth::user()?->can('view', $record->asset)) {
            return null;
        }

        return EquipmentResource::getUrl('view', ['record' => $record->asset]);
    }

    /**
     * The tag's page (it lives under its equipment), or null when the viewer may not open it.
     */
    private static function tagUrl(AssetMovement $record): ?string
    {
        if ($record->tag === null || $record->asset === null || ! Auth::user()?->can('view', $record->tag)) {
            return null;
        }

        return AssetTagResource::getUrl(
            'view',
            ['record' => $record->tag, 'asset' => $record->asset],
            configuration: EquipmentResource::pageKeyForRecord($record->asset),
        );
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Trip')
                ->schema([
                    TextEntry::make('asset.eqm_prc_code')
                        ->label('Equipment')
                        ->url(fn (AssetMovement $record): ?string => self::equipmentUrl($record))
                        ->inlineLabel(),
                    TextEntry::make('asset.eqm_name')
                        ->label('Equipment Name')
                        ->url(fn (AssetMovement $record): ?string => self::equipmentUrl($record))
                        ->inlineLabel(),
                    TextEntry::make('tag_id')
                        ->label('Tag')
                        ->placeholder('—')
                        ->url(fn (AssetMovement $record): ?string => self::tagUrl($record))
                        ->inlineLabel(),
                    TextEntry::make('status')
                        ->badge()
                        ->state(fn (AssetMovement $record): string => $record->status)
                        ->formatStateUsing(fn (string $state): string => AssetMovement::statusLabels()[$state])
                        ->color(fn (string $state): string => match ($state) {
                            AssetMovement::STATUS_ACKNOWLEDGED => 'success',
                            AssetMovement::STATUS_PENDING_ACKNOWLEDGEMENT => 'info',
                            AssetMovement::STATUS_DISREGARDED => 'gray',
                            default => 'warning',
                        })
                        ->inlineLabel(),
                    TextEntry::make('time_out')
                        ->label('Time Out')
                        ->dateTime('M d, Y h:i A')
                        ->placeholder('Missing')
                        ->inlineLabel(),
                    TextEntry::make('yardOut.name')
                        ->label('Yard Out')
                        ->placeholder('—')
                        ->inlineLabel(),
                    TextEntry::make('time_in')
                        ->label('Time In')
                        ->dateTime('M d, Y h:i A')
                        ->placeholder('Missing')
                        ->inlineLabel(),
                    TextEntry::make('yardIn.name')
                        ->label('Yard In')
                        ->placeholder('—')
                        ->inlineLabel(),
                    TextEntry::make('duration')
                        ->label('Away For')
                        ->placeholder('—')
                        ->inlineLabel(),
                ]),
            Section::make('Entered manually')
                ->columnSpanFull()
                ->visible(fn (AssetMovement $record): bool => $record->isManualOut() || $record->isManualIn())
                ->schema([
                    TextEntry::make('manualOutBy.full_name')
                        ->label('Time Out Entered By')
                        ->visible(fn (AssetMovement $record): bool => $record->isManualOut())
                        ->inlineLabel(),
                    TextEntry::make('manual_out_at')
                        ->label('Time Out Entered At')
                        ->dateTime('M d, Y h:i A')
                        ->visible(fn (AssetMovement $record): bool => $record->isManualOut())
                        ->inlineLabel(),
                    TextEntry::make('manual_out_remarks')
                        ->label('Time Out Remarks')
                        ->visible(fn (AssetMovement $record): bool => $record->isManualOut())
                        ->inlineLabel(),
                    TextEntry::make('manualInBy.full_name')
                        ->label('Time In Entered By')
                        ->visible(fn (AssetMovement $record): bool => $record->isManualIn())
                        ->inlineLabel(),
                    TextEntry::make('manual_in_at')
                        ->label('Time In Entered At')
                        ->dateTime('M d, Y h:i A')
                        ->visible(fn (AssetMovement $record): bool => $record->isManualIn())
                        ->inlineLabel(),
                    TextEntry::make('manual_in_remarks')
                        ->label('Time In Remarks')
                        ->visible(fn (AssetMovement $record): bool => $record->isManualIn())
                        ->inlineLabel(),
                ]),
            Section::make('Acknowledgement')
                ->columnSpanFull()
                ->visible(fn (AssetMovement $record): bool => $record->acknowledged_at !== null)
                ->schema([
                    TextEntry::make('acknowledgedBy.full_name')
                        ->label('Acknowledged By')
                        ->placeholder('—')
                        ->inlineLabel(),
                    TextEntry::make('acknowledged_at')
                        ->label('Acknowledged At')
                        ->dateTime('M d, Y h:i A')
                        ->inlineLabel(),
                    TextEntry::make('acknowledgement_remarks')
                        ->label('Remarks')
                        ->inlineLabel(),
                ]),
            Section::make('Disregarded')
                ->columnSpanFull()
                ->visible(fn (AssetMovement $record): bool => $record->disregarded_at !== null)
                ->schema([
                    TextEntry::make('disregardedBy.full_name')
                        ->label('Disregarded By')
                        ->placeholder('—')
                        ->inlineLabel(),
                    TextEntry::make('disregarded_at')
                        ->label('Disregarded At')
                        ->dateTime('M d, Y h:i A')
                        ->inlineLabel(),
                    TextEntry::make('disregard_remarks')
                        ->label('Reason')
                        ->inlineLabel(),
                ]),
        ]);
    }
}

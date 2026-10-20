<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Filament\Resources\EquipmentTypes\EquipmentTypeResource;
use App\Models\Equipment;
use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * The Details tab of an asset, equipment or accessory: the asset's details, then its preventive maintenance
 * schedule beside its specifications. Plain Filament sections and entries with inline labels.
 */
class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asset Details')
                    ->description('Name, code and status are synced from SAP and cannot be edited here.')
                    ->icon('heroicon-o-cube')
                    ->columns(['default' => 1, 'xl' => 2])
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('eqm_name')
                            ->label('Asset Name'),
                        TextEntry::make('eqm_prc_code')
                            ->label('Asset Code'),
                        TextEntry::make('eqm_is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                            ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('assetType.name')
                            ->label('Asset Type')
                            ->placeholder('—'),
                        TextEntry::make('lifecycleStatus.status_title')
                            ->label('Lifecycle Status')
                            ->badge()
                            ->color(fn(Equipment $record) => $record->lifecycleStatus?->status_color)
                            ->icon(fn(Equipment $record) => $record->lifecycleStatus?->status_icon)
                            ->placeholder('—'),
                        TextEntry::make('location.full_path')
                            ->label('Location')
                            // Allocated accessories show their parent equipment's location.
                            ->state(fn(Equipment $record): ?string => $record->effectiveLocation()?->full_path)
                            ->helperText(fn(Equipment $record): ?string => $record->isAllocatedAccessory()
                                ? "Inherited from {$record->parent?->eqm_name}"
                                : null)
                            ->placeholder('—'),
                        TextEntry::make('parent.eqm_name')
                            ->label('Allocated to')
                            ->visible(fn(Equipment $record): bool => $record->isAccessory())
                            ->url(fn(Equipment $record): ?string => $record->parent_id
                                ? route('filament.admin.resources.asset.view', ['record' => $record->parent_id])
                                : null)
                            ->placeholder('Unallocated'),
                        TextEntry::make('brand.eqmb_name')
                            ->label('Brand')
                            ->placeholder('—'),
                        TextEntry::make('equipmentModel.eqmm_name')
                            ->label('Model')
                            ->placeholder('—'),
                        TextEntry::make('type.eqmt_name')
                            // "Asset Type" is equipment vs accessory; this is the kind of machine.
                            ->label('Equipment Type')
                            ->url(fn(Equipment $record): ?string => $record->eqm_eqmt_id
                                ? EquipmentTypeResource::getUrl('view', ['record' => $record->eqm_eqmt_id])
                                : null)
                            ->placeholder('—'),
                        TextEntry::make('year_model')
                            ->label('Year Model')
                            ->placeholder('—'),
                        TextEntry::make('eqm_plate_num')
                            ->label('Plate Number')
                            ->placeholder('—'),
                        TextEntry::make('eqm_chassis_no')
                            ->label('Chassis #')
                            ->placeholder('—'),
                        TextEntry::make('eqm_date_purchased')
                            ->label('Date Purchased')
                            ->date('M d, Y')
                            ->placeholder('—'),
                        ViewEntry::make('categories')
                            ->label('Tags')
                            ->view('filament.infolists.entries.category-badges')
                            ->inlineLabel(),
                    ]),

                Grid::make(['default' => 1, 'xl' => 2])
                    ->schema([
                        Section::make('Preventive Maintenance Schedule')
                            ->description('Maintenance reminder configuration.')
                            ->icon('heroicon-o-bell-alert')
                            ->inlineLabel()
                            ->schema([
                                TextEntry::make('eqm_pm_itrv_type')
                                    ->label('Interval Type')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'monthly' => 'Monthly',
                                        'weekly' => 'Weekly',
                                        default => '—',
                                    })
                                    ->color(fn($state) => match ($state) {
                                        'monthly' => 'info',
                                        'weekly' => 'warning',
                                        default => 'gray',
                                    }),
                                TextEntry::make('eqm_pm_itrv_value')
                                    ->label('Every')
                                    ->formatStateUsing(
                                        fn($state, $record) => $state && $record->eqm_pm_itrv_type
                                            ? "{$state} " . ($record->eqm_pm_itrv_type === 'monthly' ? 'month(s)' : 'week(s)')
                                            : '—'
                                    ),
                                TextEntry::make('eqm_pm_itrv_start_date')
                                    ->label('PM Start Date')
                                    ->date('M d, Y')
                                    ->placeholder('—'),
                                TextEntry::make('eqm_next_pm_due_at')
                                    ->label('Next PM Due')
                                    ->date('M d, Y')
                                    ->badge()
                                    ->color(fn($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : 'info')
                                    ->placeholder('—'),
                                TextEntry::make('eqm_last_pm_notified_at')
                                    ->label('Last Notified')
                                    ->date('M d, Y')
                                    ->placeholder('—'),
                            ]),

                        Section::make('Specifications')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextEntry::make('specifications')
                                    ->hiddenLabel()
                                    ->placeholder('No specifications recorded.')
                                    ->markdown(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}

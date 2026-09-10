<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                    'lg' => 1,
                    'xl' => 2,
                ])
                    ->inlineLabel()
                    ->schema([
                        Section::make('SAP Information')
                            ->description('Data synced from SAP. These fields are read-only.')
                            ->icon('heroicon-o-server')
                            ->schema([
                                TextEntry::make('eqm_prc_code')
                                    ->label('Asset Code'),
                                TextEntry::make('eqm_name')
                                    ->label('Asset Name')
                                    ->columnSpanFull(),
                                TextEntry::make('eqm_is_active')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                                    ->icon(fn(bool $state): string => $state ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                            ]),

                        Section::make('Preventive Maintenance Schedule')
                            ->description('Maintenance reminder configuration')
                            ->icon('heroicon-o-bell-alert')
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
                                    })
                                    ->columnSpanFull(),

                                TextEntry::make('eqm_pm_itrv_value')
                                    ->label('Every')
                                    ->formatStateUsing(
                                        fn($state, $record) => $state && $record->eqm_pm_itrv_type
                                            ? "{$state} " . ($record->eqm_pm_itrv_type === 'monthly' ? 'month(s)' : 'week(s)')
                                            : '—'
                                    )
                                    ->columnSpanFull(),

                                TextEntry::make('eqm_pm_itrv_start_date')
                                    ->label('PM Start Date')
                                    ->date('M d, Y')
                                    ->columnSpanFull()
                                    ->placeholder('—'),

                                TextEntry::make('eqm_next_pm_due_at')
                                    ->label('Next PM Due')
                                    ->date('M d, Y')
                                    ->badge()
                                    ->color(fn($state) => $state && Carbon::parse($state)->isPast() ? 'danger' : 'info')
                                    ->columnSpanFull()
                                    ->placeholder('—'),

                                TextEntry::make('eqm_last_pm_notified_at')
                                    ->label('Last Notified')
                                    ->date('M d, Y')
                                    ->columnSpanFull()
                                    ->placeholder('—')
                            ]),

                        Section::make('Asset Details')
                            ->description('Additional information you can fill in manually.')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                TextEntry::make('assetType.name')
                                    ->label('Asset Type'),
                                TextEntry::make('parent.eqm_name')
                                    ->label('Allocated to')
                                    ->visible(fn(Equipment $record): bool => $record->isAccessory())
                                    ->url(fn(Equipment $record): ?string => $record->parent_id
                                        ? EquipmentResource::getUrl('view', ['record' => $record->parent_id])
                                        : null)
                                    ->placeholder('Unallocated'),
                                // TextEntry::make('categories.full_path')
                                //     ->label('Categories')
                                //     ->badge()
                                //     ->listWithLineBreaks()
                                //     ->placeholder('—'),
                                ViewEntry::make('categories')
                                    ->label('Tags')
                                    ->inlineLabel()
                                    ->view('filament.infolists.entries.category-badges'),
                                TextEntry::make('location.full_path')
                                    ->label('Location'),
                                TextEntry::make('equipmentModel.eqmm_name')
                                    ->label('Model')
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                                TextEntry::make('brand.eqmb_name')
                                    ->label('Brand')
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                                TextEntry::make('eqm_plate_num')
                                    ->label('Plate Number')
                                    ->placeholder('—'),
                                // TextEntry::make('eqm_serial_num')
                                //     ->label('Serial #')
                                //     ->placeholder('—'),
                                // TextEntry::make('eqm_engine')
                                //     ->label('Engine')
                                //     ->placeholder('—'),
                                TextEntry::make('eqm_chassis_no')
                                    ->label('Chassis #')
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                                TextEntry::make('eqm_date_purchased')
                                    ->label('Date Purchased')
                                    ->columnSpanFull()
                                    ->date('M d, Y')
                                    ->placeholder('—'),
                                TextEntry::make('specifications')
                                    ->label('Specifications')
                                    ->inlineLabel(false)
                                    ->columnSpanFull()
                                    ->placeholder('—')
                                    ->markdown()
                            ]),
                    ]),
            ])->columns(1);
    }
}

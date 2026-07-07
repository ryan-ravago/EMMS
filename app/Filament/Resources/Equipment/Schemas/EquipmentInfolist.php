<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Infolists\Components\TextEntry;
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
                    // 'lg' => 2,
                ])
                    ->inlineLabel()
                    ->schema([
                        Section::make('SAP Information')
                            ->description('Data synced from SAP. These fields are read-only.')
                            ->icon('heroicon-o-server')
                            ->schema([
                                TextEntry::make('eqm_prc_code')
                                    ->label('Equipment Code'),
                                TextEntry::make('eqm_name')
                                    ->label('Equipment Name')
                                    ->columnSpanFull(),
                                TextEntry::make('eqm_is_active')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                            ]),

                        Section::make('Equipment Details')
                            ->description('Additional information you can fill in manually.')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                TextEntry::make('model.eqmm_name')
                                    ->label('Model')
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                                TextEntry::make('eqm_vin')
                                    ->label('Vehicle Identification Number')
                                    ->placeholder('—'),
                                TextEntry::make('eqm_plate_num')
                                    ->label('Plate Number')
                                    ->placeholder('—'),
                                TextEntry::make('eqm_serial_num')
                                    ->label('Serial #')
                                    ->placeholder('—'),
                                TextEntry::make('eqm_engine')
                                    ->label('Engine')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}

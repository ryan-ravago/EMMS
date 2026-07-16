<?php

namespace App\Filament\Resources\Models\Schemas;

use App\Filament\Resources\EquipmentTypes\EquipmentTypeResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;

class ModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Model Details')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('eqmm_name')
                            ->label('Name'),
                        // TextEntry::make('type.eqmt_name')
                        //     ->label('Equipment Type')
                        //     ->url(
                        //         fn($record) => $record->eqmm_eqmt_id
                        //             ? EquipmentTypeResource::getUrl('view', ['record' => $record->eqmm_eqmt_id])
                        //             : null
                        //     )
                        //     ->color('primary')
                        //     ->icon('heroicon-m-arrow-top-right-on-square')
                        //     ->iconPosition(IconPosition::After),
                        // TextEntry::make('brand.eqmb_name')
                        //     ->label('Brand'),
                        // TextEntry::make('fuel_type.fuel_name')
                        //     ->label('Fuel Type'),
                        TextEntry::make('eqmm_max_capacity_tons')
                            ->label('Max Capacity (tons)'),
                        TextEntry::make('eqmm_max_reach_meters')
                            ->label('Max Reach (meters)'),
                    ])
            ]);
    }
}

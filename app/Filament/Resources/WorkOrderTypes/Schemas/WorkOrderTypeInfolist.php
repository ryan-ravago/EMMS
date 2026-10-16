<?php

namespace App\Filament\Resources\WorkOrderTypes\Schemas;

use App\Models\WorkOrderType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class WorkOrderTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->icon('heroicon-m-tag')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Code')
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->color('primary'),
                                TextEntry::make('type')
                                    ->label('Type')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('work_orders_count')
                                    ->label('Work Orders')
                                    ->badge()
                                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                                    ->state(fn (WorkOrderType $record): int => $record->workOrders()->count()),
                            ]),
                    ]),
            ]);
    }
}

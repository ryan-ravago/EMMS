<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Details')
                    ->schema([
                        TextEntry::make('eqmc_name')
                            ->label('Name'),
                        TextEntry::make('parent.eqmc_name')
                            ->label('Parent')
                            ->url(fn ($record) => $record->eqmc_parent_id ? CategoryResource::getUrl('view', ['record' => $record->eqmc_parent_id]) : null)
                            ->color('primary')
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->iconPosition(IconPosition::After),
                    ])
                    ->columns(2),
            ]);
    }
}

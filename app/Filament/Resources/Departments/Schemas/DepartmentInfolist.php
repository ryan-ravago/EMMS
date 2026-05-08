<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class DepartmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->icon('heroicon-m-building-office') // Adds a nice icon in the header
                    ->schema([
                        Grid::make(2) // Places Code and Name side-by-side
                            ->schema([
                                TextEntry::make('dep_code')
                                    ->label('Department Code')
                                    ->weight(FontWeight::Bold) // Makes the code stand out
                                    ->copyable() // Allows user to click to copy
                                    ->color('primary'), // Uses your theme's primary color

                                TextEntry::make('dep_name')
                                    ->label('Department Name')
                                    // ->size(TextEntrySize::Large) // Makes the name bigger
                                    ->weight(FontWeight::Bold),
                            ]),
                    ]),
            ]);
    }
}

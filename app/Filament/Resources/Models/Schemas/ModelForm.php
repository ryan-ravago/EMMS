<?php

namespace App\Filament\Resources\Models\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                ])
                    ->schema([
                        Section::make('General Information')
                            ->description('Basic identification details for this equipment model.')
                            ->icon('heroicon-o-identification')
                            ->columnSpan(1)
                            ->schema([

                                TextInput::make('eqmm_name')
                                    ->label('Model Name')
                                    ->placeholder('e.g. Caterpillar 320D')
                                    ->required()
                                    ->unique(ignoreRecord: true),

                                Select::make('eqmm_brand_id')
                                    ->label('Brand')
                                    ->relationship('brand', 'eqmb_name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->createOptionForm([
                                        TextInput::make('eqmb_name')
                                            ->label('Brand Name')
                                            ->required()
                                            ->unique(),
                                    ]),

                                Select::make('eqmm_eqmt_id')
                                    ->label('Asset Type')
                                    ->relationship('type', 'eqmt_name')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->createOptionForm([
                                        TextInput::make('eqmt_name')
                                            ->label('Type Name')
                                            ->required()
                                            ->unique()
                                            ->maxLength(255),
                                    ])
                                    ->columnSpanFull(),

                                Textarea::make('remarks')
                                    ->label('Remarks')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}

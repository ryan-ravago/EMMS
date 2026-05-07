<?php

namespace App\Filament\Resources\ChecklistTemplates\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ChecklistTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('clt_name')
                            ->label('Checklist Name')
                            ->required()
                            ->unique()
                            ->columnSpanFull(),
                        Select::make('clt_cut_id')
                            ->label('Usage Type')
                            ->relationship('checklistUsageType', 'cut_name')
                            ->required()
                            ->live(),
                        Select::make('clt_dep_id')
                            ->label('Department')
                            ->relationship('department', 'dep_name')
                            ->required()
                    ]),

                Section::make('Schedule & Interval')
                    ->columns(4)
                    ->hidden(fn(Get $get) => $get('clt_cut_id') != 2)
                    ->schema([
                        TextInput::make('clt_interval_years')
                            ->label('Years')
                            ->numeric()
                            ->minValue(0)
                            ->default(null),
                        TextInput::make('clt_interval_months')
                            ->label('Months')
                            ->numeric()
                            ->minValue(0)
                            ->default(null),
                        TextInput::make('clt_interval_weeks')
                            ->label('Weeks')
                            ->numeric()
                            ->minValue(0)
                            ->default(null),
                        TextInput::make('clt_interval_days')
                            ->label('Days')
                            ->numeric()
                            ->minValue(0)
                            ->default(null),
                        TimePicker::make('clt_schedule_time')
                            ->label('Schedule Time')
                            ->required()
                            ->seconds(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Checklist Items')
                    ->schema([
                        Repeater::make('checklistItems')
                            ->relationship('checklistItems')
                            ->schema([
                                TextInput::make('cli_name')
                                    ->label('Item Name')
                                    ->required()
                                    ->columnSpan(3),
                                TextInput::make('cli_sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(1),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['cli_created_by']      = auth()->id();
                                $data['cli_created_at']      = now();
                                $data['cli_last_updated_by'] = auth()->id();
                                $data['cli_last_updated_at'] = now();

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $data['cli_last_updated_by'] = auth()->id();
                                $data['cli_last_updated_at'] = now();

                                return $data;
                            })
                            ->columns(4)
                            ->orderColumn('cli_sort_order')
                            ->addActionLabel('Add Item')
                            ->reorderable('cli_sort_order')
                            ->cloneable()
                            ->collapsible()
                            ->defaultItems(1)
                            ->columns(3)
                            ->grid(3)
                            ->columnSpanFull(),
                    ])->columnSpanFull()
            ]);
    }
}

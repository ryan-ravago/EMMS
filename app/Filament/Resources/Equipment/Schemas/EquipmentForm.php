<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Models\AssetType;
use App\Models\Equipment;
use Carbon\Carbon;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        // Closures for PM calculations
        $clearAllPM = function (Set $set) {
            $set('eqm_pm_itrv_value', 0);
            $set('eqm_pm_itrv_start_date', null);
            $set('eqm_next_pm_due_at', null);
        };

        $clearDueDate = function (Set $set) {
            $set('eqm_next_pm_due_at', null);
        };

        $calculateNextPMDue = function (Set $set, Get $get) {
            $type = $get('eqm_pm_itrv_type');
            $value = $get('eqm_pm_itrv_value');
            $startDate = $get('eqm_pm_itrv_start_date');

            // If all 3 exist, calculate next due
            if ($type && $value && $startDate) {
                $base = Carbon::parse($startDate);
                $nextDue = $type === 'monthly'
                    ? $base->addMonths($value)
                    : $base->addWeeks($value);

                $set('eqm_next_pm_due_at', $nextDue->format('Y-m-d'));
            } else {
                // Any missing, clear due date
                $set('eqm_next_pm_due_at', null);
            }
        };

        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                    'lg' => 1,
                    'xl' => 2,
                ])
                    ->schema([
                        Section::make('SAP Information')
                            ->description('Data synced from SAP. These fields are read-only.')
                            ->icon('heroicon-o-server')
                            ->schema([
                                TextInput::make('eqm_prc_code')
                                    ->label('Equipment Code')
                                    ->disabled()
                                    ->dehydrated(), // Occupies half-width on the first row,
                                TextInput::make('eqm_name')
                                    ->label('Equipment Name')
                                    ->disabled()
                                    ->dehydrated(),
                                Toggle::make('eqm_is_active')
                                    ->label('Active')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ]),

                        Section::make('Preventive Maintenance Schedule')
                            ->description('Set up automatic maintenance reminders')
                            ->icon('heroicon-o-bell-alert')
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                Select::make('eqm_pm_itrv_type')
                                    ->label('Interval Type')
                                    ->options([
                                        'monthly' => 'Monthly',
                                        'weekly' => 'Weekly',
                                    ])
                                    ->required(fn (Get $get) => $get('eqm_pm_itrv_value') || $get('eqm_pm_itrv_start_date'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) use ($clearAllPM, $calculateNextPMDue) {
                                        if (! $state) {
                                            $clearAllPM($set);
                                        } else {
                                            $calculateNextPMDue($set, $get);
                                        }
                                    })
                                    ->native(false),

                                TextInput::make('eqm_pm_itrv_value')
                                    ->label('Every')
                                    ->numeric()
                                    ->minValue(function ($state, Set $set, Get $get) {
                                        if ($get('eqm_pm_itrv_type') || $get('eqm_pm_itrv_start_date')) {
                                            return 1;
                                        } else {
                                            return 0;
                                        }
                                    })
                                    ->suffix(
                                        fn (Get $get) => $get('eqm_pm_itrv_type')
                                            ? ($get('eqm_pm_itrv_type') === 'monthly' ? 'month(s)' : 'week(s)')
                                            : ''
                                    )
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) use ($clearDueDate, $calculateNextPMDue) {
                                        if (! $state || $state < 1) {
                                            $clearDueDate($set);
                                        } else {
                                            $calculateNextPMDue($set, $get);
                                        }
                                    }),

                                DatePicker::make('eqm_pm_itrv_start_date')
                                    ->label('PM Start Date')
                                    ->native(false)
                                    ->live()
                                    ->columnStart(1)
                                    ->required(fn (Get $get) => $get('eqm_pm_itrv_type') || $get('eqm_pm_itrv_value'))
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) use ($clearDueDate, $calculateNextPMDue) {
                                        if (! $state) {
                                            $clearDueDate($set);
                                        } else {
                                            $calculateNextPMDue($set, $get);
                                        }
                                    }),

                                DatePicker::make('eqm_next_pm_due_at')
                                    ->label('Next PM Due')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnStart(1)
                                    ->native(false),

                                DatePicker::make('eqm_last_pm_notified_at')
                                    ->label('Last Notified')
                                    ->disabled()
                                    ->dehydrated()
                                    ->native(false),
                            ]),
                    ]),

                Grid::make([
                    'default' => 1,
                    'md' => 2,
                    'lg' => 1,
                    'xl' => 2,
                ])
                    ->schema([
                        Section::make('Equipment Details')
                            ->description('Additional information you can fill in manually.')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                ToggleButtons::make('asset_type_id')
                                    ->label('Asset Type')
                                    ->inline()
                                    ->options(AssetType::orderBy('name')->pluck('name', 'id'))
                                    ->colors([
                                        1 => 'warning',
                                        2 => 'success',
                                    ])
                                    ->live()
                                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                                        if ((int) $state !== (int) AssetType::accessoryId()) {
                                            $set('parent_id', null);
                                        }
                                    })
                                    ->required(),
                                Select::make('parent_id')
                                    ->label('Allocated to')
                                    ->relationship(
                                        'parent',
                                        'eqm_name',
                                        fn (Builder $query): Builder => $query->equipmentAssets(),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Equipment $record): string => trim(
                                            ($record->eqm_prc_code ? "{$record->eqm_prc_code} — " : '').$record->eqm_name
                                        )
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->visible(fn (Get $get): bool => (int) $get('asset_type_id') === (int) AssetType::accessoryId())
                                    ->helperText('Accessories can only be allocated to equipment.'),
                                SelectTree::make('categories')
                                    ->label('Tags')
                                    ->relationship('categories', 'eqmc_name', 'eqmc_parent_id')
                                    ->multiple()
                                    ->enableBranchNode(),
                                // ->getOptionLabelFromRecordUsing(function (EquipmentCategory $record): string {
                                //     return $record->full_path;
                                // }),
                                // ->preload()
                                // ->searchable()
                                // ->native(false),
                                // SelectTree::make('eqmc_parent_id')
                                //     ->label('Category')
                                //     ->relationship('parent', 'eqmc_name', 'eqmc_parent_id'),
                                // ->required()
                                // ->native(false)
                                // ->preload(),
                                SelectTree::make('location_id')
                                    ->label('Location')
                                    ->enableBranchNode()
                                    ->relationship('location', 'name', 'parent_id'),
                                // ->required()
                                // ->native(false)
                                // ->preload(),
                                Select::make('eqm_eqmm_id')
                                    ->label('Model')
                                    ->relationship('equipmentModel', 'eqmm_name')
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('eqmm_name')
                                            ->label('Model Name')
                                            ->required()
                                            ->unique(),
                                        // Select::make('eqmm_brand_id')
                                        //     ->label('Brand')
                                        //     ->relationship('brand', 'eqmb_name')
                                        //     ->native(false)
                                        //     ->createOptionForm([
                                        //         TextInput::make('eqmb_name')
                                        //             ->label('Brand Name')
                                        //             ->required()
                                        //             ->unique(),
                                        //     ])
                                        //     ->createOptionAction(function (Action $action) {
                                        //         return $action
                                        //             ->modalHeading('Add New Equipment Brand')
                                        //             ->modalWidth('md'); // xs, sm, md, lg, xl, 2xl
                                        //     }),
                                        // Select::make('eqm_eqmt_id')
                                        //     ->label('Type')
                                        //     ->relationship('type', 'eqmt_name')
                                        //     ->native(false)
                                        //     ->required()
                                        //     ->createOptionForm([
                                        //         TextInput::make('eqmt_name')
                                        //             ->label('Type Name')
                                        //             ->required()
                                        //             ->unique()
                                        //             ->maxLength(255),
                                        //     ])
                                        //     ->createOptionAction(function (Action $action) {
                                        //         return $action
                                        //             ->modalHeading('Add New Equipment Type')
                                        //             ->modalWidth('md');
                                        //     }),
                                        // Select::make('eqmm_fuel_type')
                                        //     ->label('Fuel Type')
                                        //     ->relationship('fuel_type', 'fuel_name')
                                        //     ->native(false),
                                    ])
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Add New Equipment Model')
                                            ->modalWidth('md'); // xs, sm, md, lg, xl, 2xl
                                    })
                                    ->columnSpanFull(),

                                Select::make('eqm_brand_id')
                                    ->label('Brand')
                                    ->relationship('brand', 'eqmb_name')
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('eqmb_name')
                                            ->label('Brand Name')
                                            ->required()
                                            ->unique(),
                                    ])
                                    ->createOptionAction(function (Action $action) {
                                        return $action
                                            ->modalHeading('Add New Equipment Brand')
                                            ->modalWidth('md'); // xs, sm, md, lg, xl, 2xl
                                    }),
                                TextInput::make('eqm_plate_num')
                                    ->label('Plate #')
                                    ->placeholder('e.g. ABC 1234')
                                    ->columnSpanFull(),

                                // TextInput::make('eqm_serial_num')
                                //     ->label('Serial #')
                                //     ->placeholder('e.g. SN-000123')
                                //     ->unique()
                                //     ->maxLength(255)
                                //     ->columnSpanFull(),

                                TextInput::make('eqm_chassis_no')
                                    ->label('Chassis #')
                                    ->columnStart(1)
                                    ->extraAttributes(['class' => 'max-w-lg'])
                                    ->maxLength(255),

                                TextInput::make('year_model')
                                    ->label('Year Model')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue((int) date('Y') + 1)
                                    ->nullable(),

                                DatePicker::make('eqm_date_purchased')
                                    ->label('Date Purchased')
                                    ->columnStart(1)
                                    ->extraAttributes(['class' => 'max-w-lg'])
                                    ->native(false)
                                    ->maxDate(today()),

                                RichEditor::make('specifications')
                                    ->label('Specifications')
                                    ->placeholder('Enter asset specifications...')
                                    ->extraAttributes([
                                        'style' => 'min-height: 300px;',
                                    ]),
                            ]),
                    ]),
            ])->columns(1);
    }
}

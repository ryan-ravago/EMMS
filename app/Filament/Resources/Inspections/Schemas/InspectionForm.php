<?php

namespace App\Filament\Resources\Inspections\Schemas;

use App\Models\AppUser;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentTaskChecklistTemplate;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class InspectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inspection Details')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('ins_dep_id')
                            ->label('Department')
                            ->options(Department::pluck('dep_name', 'dep_id'))
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->visible(fn() => auth()->user()->hasRole('super_admin'))
                            ->default(fn() => auth()->user()->hasRole('super_admin') ? null : auth()->user()->user_dep_id)
                            ->live(),

                        Select::make('ins_eqm_id')
                            ->label('Equipment')
                            ->hint('Only equipment with templates are shown')
                            ->hintColor('gray')
                            ->required()
                            ->preload()
                            ->native(false)
                            ->searchable()
                            ->relationship(
                                name: 'equipment',
                                titleAttribute: 'eqm_name',
                                modifyQueryUsing: function ($query, Get $get) {
                                    $depId = auth()->user()->hasRole('super_admin')
                                        ? $get('ins_dep_id')
                                        : auth()->user()->user_dep_id;

                                    return $query
                                        ->where('eqm_is_active', true)
                                        ->whereHas('equipmentTaskChecklistTemplates', fn($q) => $q->where('etct_dep_id', $depId));
                                }
                            )
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $depId = auth()->user()->hasRole('super_admin')
                                    ? $get('ins_dep_id')
                                    : auth()->user()->user_dep_id;

                                if (!$state || !$depId) {
                                    $set('inspection_items', []);
                                    return;
                                }

                                $items = EquipmentTaskChecklistTemplate::with('task')
                                    ->where('etct_eqm_id', $state)
                                    ->where('etct_dep_id', $depId)
                                    ->get()
                                    ->map(fn($template) => [
                                        'insi_cli_name_for_record' => $template->task->task_name,
                                        'insi_task_id'             => $template->etct_task_id,
                                        'insi_result'              => null,
                                        'insi_remarks'             => null,
                                    ])
                                    ->toArray();

                                $set('inspection_items', $items);
                            }),

                        Select::make('ins_by')
                            ->label('Inspected By')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->relationship(
                                name: 'conductedBy',
                                titleAttribute: 'user_fname',
                                modifyQueryUsing: function ($query, Get $get) {
                                    $depId = auth()->user()->hasRole('super_admin')
                                        ? $get('ins_dep_id')
                                        : auth()->user()->user_dep_id;

                                    return $query
                                        ->whereHas('roles', fn($q) => $q->where('name', 'technician'))
                                        ->where('user_dep_id', $depId);
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->user_fname} {$record->user_lname}"),

                        DateTimePicker::make('ins_dt')
                            ->label('Inspection Date & Time')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('M d, Y | h:i A')
                            ->default(now())
                            ->maxDate(now()),
                    ]),

                Section::make('Inspection Tasks')
                    ->icon('heroicon-o-list-bullet')
                    ->visible(fn(Get $get) => filled($get('ins_eqm_id')))
                    ->columnSpanfull()
                    ->schema([
                        Repeater::make('inspection_items')
                            ->hiddenLabel()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(2)
                            ->grid(3)
                            ->default(function (Get $get) {
                                $eqmId = $get('ins_eqm_id');
                                $depId = auth()->user()->hasRole('super_admin')
                                    ? $get('ins_dep_id')
                                    : auth()->user()->user_dep_id;

                                if (!$eqmId || !$depId) return [];

                                return EquipmentTaskChecklistTemplate::with('task')
                                    ->where('etct_eqm_id', $eqmId)
                                    ->where('etct_dep_id', $depId)
                                    ->get()
                                    ->map(fn($template) => [
                                        'insi_cli_name_for_record' => $template->task->task_name,
                                        'insi_task_id'             => $template->etct_task_id,
                                        'insi_result'              => null,
                                        'insi_remarks'             => null,
                                    ])
                                    ->toArray();
                            })
                            ->schema([
                                TextInput::make('insi_cli_name_for_record')
                                    ->label('Task')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                ToggleButtons::make('insi_result')
                                    ->label('Result')
                                    ->options([
                                        'P' => 'Passed',
                                        'F' => 'Failed',
                                        'N' => 'N/A',
                                    ])
                                    ->colors([
                                        'P' => 'success',
                                        'F' => 'danger',
                                        'N' => 'gray',
                                    ])
                                    ->icons([
                                        'P' => 'heroicon-o-check-circle',
                                        'F' => 'heroicon-o-x-circle',
                                        'N' => 'heroicon-o-minus-circle',
                                    ])
                                    ->required()
                                    ->inline()
                                    ->columnSpan(2),
                                // Select::make('insi_result')
                                //     ->label('Result')
                                //     ->options([
                                //         'P' => 'Passed',
                                //         'F' => 'Failed',
                                //         'N' => 'N/A',
                                //     ])
                                //     ->required()
                                //     ->native(false)
                                //     ->live()
                                //     ->columnSpan(1),
                                Textarea::make('insi_remarks')
                                    ->label('Remarks')
                                    ->nullable()
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\WorkOrders\Schemas;

use App\Models\AppUser;
use App\Models\Equipment;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderForm
{
    protected static function resolveDepartmentId(Get $get): ?int
    {
        return auth()->user()->hasRole('super_admin')
            ? $get('wo_dep_id')
            : auth()->user()->user_dep_id;
    }

    protected static function activeTechnicians(?int $depId)
    {
        if (! $depId) {
            return AppUser::whereRaw('1 = 0')->get();
        }

        return AppUser::whereHas('roles', fn($q) => $q->where('name', 'technician'))
            ->where('user_dep_id', $depId)
            ->where('is_active', 1)
            ->get();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Work Order Form')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->schema([
                            Select::make('wo_eqm_id')
                                ->label('Equipment')
                                ->relationship(
                                    'equipment',
                                    'eqm_name',
                                    fn(Builder $query) => $query
                                        ->equipmentAssets()
                                        ->where('eqm_is_active', 1)
                                )
                                ->searchable(['eqm_name', 'eqm_prc_code'])
                                ->getOptionLabelFromRecordUsing(function (Equipment $record): string {
                                    return "($record->eqm_prc_code) {$record->eqm_name}";
                                })
                                ->preload()
                                ->required()
                                ->native(false)
                                ->live(),
                            Select::make('wo_dep_id')
                                ->label('Department')
                                ->relationship(
                                    'department',
                                    'dep_name',
                                    fn(Builder $query) => $query->where('is_maintenance', 1)
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false)
                                ->live()
                                ->visible(fn() => auth()->user()->hasRole('super_admin')),
                            Select::make('wo_prio_id')
                                ->label('Priority')
                                ->relationship('priority', 'prio_name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->native(false),
                            // TextInput::make('wo_title')
                            //     ->label('Subject')
                            //     ->required(),
                            Textarea::make('wo_desc')
                                ->label('Problem Description')
                                ->required()
                                ->rows(5)
                                ->autosize()
                                ->placeholder('Provide detailed request here')
                                ->columnSpanFull(),
                            FileUpload::make('wo_attachments')
                                ->label('Attachments')
                                ->multiple()
                                ->nullable()
                                ->columnSpanFull()
                                ->maxFiles(10)
                                ->maxParallelUploads(5)
                                ->panelLayout('grid')
                                ->reorderable()
                                ->appendFiles()
                                ->openable()
                                ->downloadable()
                                ->previewable()
                                ->maxSize(10240) // 10MB per file
                                // ->acceptedFileTypes([
                                //     'image/*',
                                //     'application/pdf',
                                //     'application/msword',
                                //     'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                //     'application/vnd.ms-excel',
                                //     'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                //     'text/csv',
                                // ])
                                ->imageEditor(),

                        ]),

                    Step::make('Assign Workers')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Select::make('worker_ids')
                                ->label('Assigned Workers')
                                ->relationship('workers', 'user_fname')
                                ->options(fn(Get $get) => static::activeTechnicians(static::resolveDepartmentId($get))
                                    ->mapWithKeys(fn($user) => [
                                        $user->user_id => "{$user->user_fname} {$user->user_lname}",
                                    ]))
                                ->multiple()
                                ->required()
                                ->native(false)
                                ->searchable()
                                ->rules([
                                    fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get): void {
                                        $depId = static::resolveDepartmentId($get);
                                        $validIds = static::activeTechnicians($depId)->pluck('user_id')->all();
                                        $invalid = array_diff((array) $value, $validIds);

                                        if (! empty($invalid)) {
                                            $fail('One or more selected workers are not active technicians for this department.');
                                        }
                                    },
                                ]),
                        ]),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\WorkOrders\Schemas;

use App\Models\AppUser;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Builder;

class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Work Order Details')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->schema([
                            Section::make('Equipment')
                                ->icon('heroicon-o-truck')
                                ->columns(2)
                                ->columnSpanFull()
                                ->schema([
                                    Select::make('wo_eqm_id')
                                        ->label('Equipment')
                                        ->relationship('equipment', 'eqm_name')
                                        ->searchable()
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
                                ]),

                            Section::make('Details')
                                ->icon('heroicon-o-document-text')
                                ->columns(2)
                                ->columnSpanFull()
                                ->schema([
                                    Textarea::make('wo_title')
                                        ->label('Title')
                                        ->required(),
                                    Select::make('wo_prio_id')
                                        ->label('Priority')
                                        ->relationship('priority', 'prio_name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false),
                                    TextArea::make('wo_desc')
                                        ->label('Description')
                                        ->required()
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
                        ]),

                    Step::make('Assign Workers')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Select::make('worker_ids')
                                ->label('Assigned Workers')
                                ->relationship('workers', 'user_fname')
                                ->options(function (Get $get) {
                                    $depId = auth()->user()->hasRole('super_admin')
                                        ? $get('wo_dep_id')
                                        : auth()->user()->user_dep_id;

                                    if (!$depId) return [];

                                    return AppUser::whereHas('roles', fn($q) => $q->where('name', 'technician'))
                                        ->where('user_dep_id', $depId)
                                        ->get()
                                        ->mapWithKeys(fn($user) => [
                                            $user->user_id => "{$user->user_fname} {$user->user_lname}"
                                        ]);
                                })
                                ->multiple()
                                ->required()
                                ->native(false)
                                ->searchable(),
                        ])
                ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }
}

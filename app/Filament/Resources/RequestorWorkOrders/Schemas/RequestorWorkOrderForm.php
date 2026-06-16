<?php

namespace App\Filament\Resources\RequestorWorkOrders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RequestorWorkOrderForm
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
                                            fn (Builder $query) => $query->where('is_maintenance', 1)
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false)
                                        ->live(),
                                ]),

                            Section::make('Details')
                                ->icon('heroicon-o-document-text')
                                ->columns(2)
                                ->columnSpanFull()
                                ->schema([
                                    Textarea::make('wo_title')
                                        ->label('Title')
                                        ->required()
                                        ->columnSpanFull(),
                                    Textarea::make('wo_req_desc')
                                        ->label('Description')
                                        ->required()
                                        ->columnSpanFull(),
                                    Select::make('wo_prio_id')
                                        ->label('Priority')
                                        ->relationship('priority', 'prio_name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false),
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
                                        ->maxSize(10240)
                                        ->imageEditor(),
                                ]),
                        ]),
                ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\RequestorWorkOrders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RequestorWorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Work Order Form')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->iconColor('primary')
                    ->schema([
                        Select::make('wo_eqm_id')
                            ->label('Equipment')
                            ->relationship('equipment', 'eqm_name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->required()
                            ->native(false)
                            ->live(),
                        Select::make('wo_dep_id')
                            ->label('Department')
                            ->placeholder('Department to be assigned')
                            ->relationship(
                                'department',
                                'dep_name',
                                fn(Builder $query) => $query->where('is_maintenance', 1)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->columnSpanFull()
                            ->live(),
                        Select::make('wo_prio_id')
                            ->label('Priority')
                            ->relationship('priority', 'prio_name')
                            ->searchable()
                            ->columnSpanFull()
                            ->preload()
                            ->required()
                            ->native(false),
                        // TextInput::make('wo_title')
                        //     ->label('Subject')
                        //     ->required()
                        //     ->columnSpanFull(),
                        Textarea::make('wo_req_desc')
                            ->label('Problem Description')
                            ->required()
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
                            ->maxSize(10240)
                            ->imageEditor(),

                    ])
                    ->columns(2),
            ]);
    }
}

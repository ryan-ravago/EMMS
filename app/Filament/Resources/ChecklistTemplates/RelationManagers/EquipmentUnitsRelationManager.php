<?php

namespace App\Filament\Resources\ChecklistTemplates\RelationManagers;

use App\Filament\Resources\Equipment\EquipmentResource;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentUnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'equipmentUnits';

    // protected static ?string $relatedResource = EquipmentResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eqm_prc_code')
                    ->label('PRC Code')
                    ->searchable(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('model.eqmm_name')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('eqm_vin')
                    ->label('VIN')
                    ->searchable(),
                TextColumn::make('eqm_plate_num')
                    ->label('Plate #')
                    ->searchable()
            ])
            ->headerActions([
                // CreateAction::make(),
                AttachAction::make()
                    ->recordSelectSearchColumns(['eqm_name'])
                    ->recordTitleAttribute('eqm_name')
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Equipment\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
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
                    ->label('Vehicle Identification Number')
                    ->searchable(),
                TextColumn::make('eqm_plate_num')
                    ->label('Plate #')
                    ->searchable(),
                IconColumn::make('eqm_is_active')
                    ->label('Active')
                    ->boolean()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Models\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eqmm_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type.eqmt_name')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.eqmb_name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fuel_type.fuel_name')
                    ->label('Fuel Type')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('eqmm_max_capacity_tons')
                    ->label('Max Capacity (tons)')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('eqmm_max_reach_meters')
                    ->label('Max Reach (meters)')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

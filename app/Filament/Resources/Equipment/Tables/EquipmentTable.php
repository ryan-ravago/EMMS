<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Resources\Equipment\EquipmentResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eqm_prc_code')
                    ->label('PRC Code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('model.eqmm_name')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('eqm_vin')
                    ->label('VIN')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('eqm_plate_num')
                    ->label('Plate #')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('eqm_is_active')
                    ->label('Active')
                    ->sortable()
                    ->boolean()
            ])
            ->filters([
                SelectFilter::make('eqm_is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                SelectFilter::make('eqm_eqmm_id')
                    ->label('Model')
                    ->relationship('model', 'eqmm_name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->recordUrl(
                fn(Model $record): string => EquipmentResource::getUrl('view', ['record' => $record]),
            )
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Models\Resources\Equipment\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
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
                    ->label('Equipment Code')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('eqm_name')
                    ->label('Name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('eqm_is_active')
                    ->label('Status')
                    ->toggleable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                    ->icon(fn(bool $state): Heroicon => $state ? Heroicon::CheckCircle : Heroicon::XCircle)
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('equipmentModel.eqmm_name')
                    ->label('Model')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('eqm_vin')
                //     ->label('VIN')
                //     ->toggleable()
                //     ->sortable()
                //     ->searchable(),
                TextColumn::make('eqm_plate_num')
                    ->label('Plate #')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('eqm_name', 'asc')
            ->filters([
                SelectFilter::make('eqm_is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                SelectFilter::make('eqm_eqmm_id')
                    ->label('Model')
                    ->relationship('equipmentModel', 'eqmm_name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            // ->recordUrl(
            //     fn(Model $record): string => EquipmentResource::getUrl('view', ['record' => $record]),
            // )
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

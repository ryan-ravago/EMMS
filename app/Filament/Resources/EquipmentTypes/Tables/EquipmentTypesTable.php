<?php

namespace App\Filament\Resources\EquipmentTypes\Tables;

use App\Filament\Resources\EquipmentTypes\EquipmentTypeResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EquipmentTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('eqmt_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])->recordUrl(
                fn(Model $record): string => EquipmentTypeResource::getUrl('view', ['record' => $record]),
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

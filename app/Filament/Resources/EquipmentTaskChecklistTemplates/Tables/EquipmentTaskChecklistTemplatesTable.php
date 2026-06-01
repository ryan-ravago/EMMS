<?php

namespace App\Filament\Resources\EquipmentTaskChecklistTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentTaskChecklistTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('etct_dep_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('etct_eqm_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('etct_task_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('etct_created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('etct_created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

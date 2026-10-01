<?php

namespace App\Filament\Resources\Locations\Tables;

use Alareqi\FilamentTree\Columns\TreeColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TreeColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                // TreeColumn::make('full_path')
                //     ->label('Full Path'),
            ])
            ->tree(
                parentColumn: 'parent_id',
                treeColumn: 'name',
            )
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

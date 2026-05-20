<?php

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task_name')
                    ->searchable(),
                TextColumn::make('task_dep_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('task_tut_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('task_created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('task_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('task_last_updated_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('task_last_updated_at')
                    ->dateTime()
                    ->sortable(),
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

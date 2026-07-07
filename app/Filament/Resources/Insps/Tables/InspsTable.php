<?php

namespace App\Filament\Resources\Insps\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InspsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('insp_id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('insp_no')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('insp_dep_id')
                    ->searchable(),
                TextColumn::make('insp_eqm_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('is_submitted')
                    ->searchable(),
                TextColumn::make('checklist_template_name')
                    ->searchable(),
                TextColumn::make('insp_by')
                    ->searchable(),
                TextColumn::make('insp_submitted_by')
                    ->searchable(),
                TextColumn::make('insp_submitted_at')
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

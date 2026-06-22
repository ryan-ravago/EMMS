<?php

namespace App\Filament\Resources\Inspections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InspectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ins_no')
                    ->label('Inspection #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('equipment.eqm_name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
                TextColumn::make('conductedBy.user_fname')
                    ->label('Inspected by')
                    ->formatStateUsing(fn ($record) => "{$record->conductedBy->user_fname} {$record->conductedBy->user_lname}"),
                TextColumn::make('ins_dt')
                    ->label('Inspection Date')
                    ->dateTime('M d, Y | h:i A')
                    ->sortable(),
                TextColumn::make('submittedBy.user_fname')
                    ->label('Submitted by')
                    ->formatStateUsing(fn ($record) => $record->submittedBy
                        ? "{$record->submittedBy->user_fname} {$record->submittedBy->user_lname}"
                        : '—'),
                TextColumn::make('ins_submitted_dt')
                    ->label('Submitted at')
                    ->dateTime('M d, Y | h:i A')
                    ->sortable(),
                TextColumn::make('inspection_items_count')
                    ->label('Items Count')
                    ->counts('inspectionItems')
                    ->badge(),
            ])
            ->defaultSort('ins_dt', 'desc')
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

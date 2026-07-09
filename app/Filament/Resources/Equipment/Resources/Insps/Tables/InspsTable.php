<?php

namespace App\Filament\Resources\Equipment\Resources\Insps\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InspsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = auth()->user();

                if ($user === null) {
                    return $query->whereRaw('1 = 0');
                }

                if ($user->hasRole('super_admin')) {
                    return $query;
                }

                return $query->where('insp_dep_id', $user->user_dep_id);
            })
            ->columns([
                TextColumn::make('insp_no')
                    ->label('Inspection #')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('insp_dep_id')
                    ->visible(fn() => auth()->user()->hasRole(['super_admin']))
                    ->searchable(),
                TextColumn::make('equipment.eqm_name')
                    ->label('Equipment Unit')
                    ->sortable(),
                TextColumn::make('insp_by')
                    ->label('Inspector')
                    ->searchable(),
                TextColumn::make('insp_submitted_by')
                    ->label('Submitted By')
                    ->searchable(),
                TextColumn::make('insp_submitted_at')
                    ->label('Submitted On')
                    ->dateTime('M d, Y h:i A')
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

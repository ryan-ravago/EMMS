<?php

namespace App\Filament\Resources\AppUsers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_fname')
                    ->label('First Name')
                    ->searchable(),
                TextColumn::make('user_mname')
                    ->label('Middle Name')
                    ->searchable(),
                TextColumn::make('user_lname')
                    ->label('Last Name')
                    ->searchable(),
                TextColumn::make('user_email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('user_contact_no')
                    ->label('Contact')
                    ->searchable(),
                TextColumn::make('user_dep_id')
                    ->numeric()
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

<?php

namespace App\Filament\Resources\AdminManagers\Tables;

use App\Filament\Resources\AdminManagers\AdminManagerResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AdminManagersTable
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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_lname')
                    ->label('Last Name')
                    ->searchable(),
                TextColumn::make('user_email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('user_contact_no')
                    ->label('Contact')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('roles.display_name')
                    ->label('Roles')
                    ->badge()
                    ->color('warning')
                    ->separator(',')
                    ->searchable()
                    ->wrap(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordUrl(
                fn (Model $record): string => AdminManagerResource::getUrl('view', ['record' => $record]),
            )
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

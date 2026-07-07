<?php

namespace App\Filament\Resources\Technicians\Tables;

use App\Filament\Resources\Technicians\TechnicianResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TechniciansTable
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
                    ->sortable(),
                TextColumn::make('roles.display_name')
                    ->label('Roles')
                    ->badge()
                    ->color('warning')
                    ->separator(',')
                    ->searchable()
                    ->wrap(),
            ])
            ->recordUrl(
                fn (Model $record): string => TechnicianResource::getUrl('view', ['record' => $record]),
            )
            ->filters([
                SelectFilter::make('role')
                    ->relationship('roles', 'display_name')
                    ->searchable()
                    ->preload(),
            ])
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

<?php

namespace App\Filament\Resources\AppUsers\Tables;

use App\Filament\Resources\AppUsers\AppUserResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use STS\FilamentImpersonate\Actions\Impersonate;

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
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('warning')
                    ->separator(',')
                    ->searchable()
                    ->wrap()
                    ->formatStateUsing(fn(string $state): string => str($state)->replace('_', ' ')->title()),
            ])
            ->recordUrl(
                fn(Model $record): string => AppUserResource::getUrl('view', ['record' => $record]),
            )
            ->filters([
                SelectFilter::make('role')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
            ])
            // ->groups([
            //     Group::make('department.dep_name')
            //         ->label('Department')
            // ])
            // ->defaultGroup('department.dep_name')
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View'),
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit'),
                Impersonate::make()
                    ->iconButton()
                    ->tooltip('Impersonate'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

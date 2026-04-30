<?php

namespace App\Filament\Resources\Departments\RelationManagers;

use App\Filament\Resources\AppUsers\AppUserResource;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    // protected static ?string $relatedResource = AppUserResource::class;

    public function table(Table $table): Table
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
            ->headerActions([
                CreateAction::make()
                    ->authorize(true)
                    ->schema([
                        Section::make('Personal Information')
                            ->description('Basic details of the new user.')
                            ->schema([
                                Grid::make(3) // Splits into 3 columns
                                    ->schema([
                                        TextInput::make('user_fname')
                                            ->label('First Name')
                                            ->required(),
                                        TextInput::make('user_mname')
                                            ->label('Middle Name'),
                                        TextInput::make('user_lname')
                                            ->label('Last Name')
                                            ->required(),
                                    ]),

                                Grid::make(2) // Splits into 2 columns
                                    ->schema([
                                        TextInput::make('user_email')
                                            ->label('Email')
                                            ->email()
                                            ->required(),
                                        TextInput::make('user_contact_no')
                                            ->label('Contact #'),
                                    ]),
                            ]),

                        Section::make('Organization & Access')
                            ->schema([
                                Select::make('user_dep_id')
                                    ->label('Department')
                                    ->relationship('department', 'dep_name')
                                    ->default(fn() => $this->getOwnerRecord()->getKey())
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->native(false),

                                TextInput::make('user_fb_profile_link')
                                    ->label('Facebook Profile Link')
                                    ->url() // Validates it is a link
                                    ->placeholder('https://facebook.com/...'),

                                CheckboxList::make('roles')
                                    ->label('Assigned Roles')
                                    ->relationship('roles', 'name')
                                    ->getOptionLabelFromRecordUsing(
                                        fn($record) => str($record->name)->replace('_', ' ')->title()
                                    )
                                    ->columns(2) // Makes the checkboxes appear in 2 columns
                                    ->searchable()
                                    ->required()
                                    ->gridDirection('row'),
                            ]),
                    ]),
            ]);
    }
}

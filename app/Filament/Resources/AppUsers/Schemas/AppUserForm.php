<?php

namespace App\Filament\Resources\AppUsers\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AppUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_fname')
                    ->required(),
                TextInput::make('user_mname')
                    ->required(),
                TextInput::make('user_lname')
                    ->required(),
                TextInput::make('user_email')
                    ->email()
                    ->required(),
                TextInput::make('user_dep_id')
                    ->required(),
                TextInput::make('user_contact_no')
                    ->default(null),
                TextInput::make('user_fb_profile_link')
                    ->default(null),
                CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->searchable(),
            ]);
    }
}

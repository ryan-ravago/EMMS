<?php

namespace App\Filament\Resources\Technicians\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TechnicianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_fname')
                    ->required(),
                TextInput::make('user_mname')
                    ->default(null),
                TextInput::make('user_lname')
                    ->required(),
                TextInput::make('user_email')
                    ->email()
                    ->required(),
                TextInput::make('user_avatar')
                    ->default(null),
                TextInput::make('user_contact_no')
                    ->default(null),
                Textarea::make('user_fb_profile_link')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('user_dep_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}

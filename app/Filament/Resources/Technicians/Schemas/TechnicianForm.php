<?php

namespace App\Filament\Resources\Technicians\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TechnicianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('user_fname')
                            ->label('First Name')
                            ->required(),
                        TextInput::make('user_mname')
                            ->label('Middle Name')
                            ->default(null),
                        TextInput::make('user_lname')
                            ->label('Last Name')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Contact Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('user_email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('user_contact_no')
                            ->label('Contact #')
                            ->default(null),
                        Textarea::make('user_fb_profile_link')
                            ->label('Facebook Profile Link')
                            ->default(null)
                            ->columnSpanFull(),
                    ]),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }
}

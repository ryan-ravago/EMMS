<?php

namespace App\Filament\Resources\AppUsers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;

use function Laravel\Prompts\grid;

class AppUserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Personal Information')
                    ->columns(3)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('user_fname')
                            ->label('First Name'),
                        TextEntry::make('user_mname')
                            ->label('Middle Name')
                            ->placeholder('-'),
                        TextEntry::make('user_lname')
                            ->label('Last Name'),
                    ]),

                Section::make('Contact Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user_email')
                            ->label('Email'),
                        TextEntry::make('user_contact_no')
                            ->label('Contact #')
                            ->placeholder('-'),
                        TextEntry::make('user_fb_profile_link')
                            ->label('Facebook Profile Link')
                            ->placeholder('-')
                            ->url(fn($record) => $record->user_fb_profile_link)
                            ->openUrlInNewTab()
                            ->color('info')
                    ]),

                Section::make('Application Access')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('department.dep_name')
                            ->label('Department'),
                        TextEntry::make('roles.name')
                            ->label('Roles')
                            ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title())
                            ->badge()
                            ->separator(','),
                    ]),
            ]);
    }
}

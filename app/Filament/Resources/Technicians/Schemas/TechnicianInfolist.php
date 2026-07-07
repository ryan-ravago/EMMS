<?php

namespace App\Filament\Resources\Technicians\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TechnicianInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user_fname')
                            ->label('First Name'),
                        TextEntry::make('user_mname')
                            ->label('Middle Name')
                            ->placeholder('-'),
                        TextEntry::make('user_lname')
                            ->label('Last Name')
                            ->columnSpanFull(),
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
                            ->url(fn ($record) => $record->user_fb_profile_link)
                            ->openUrlInNewTab()
                            ->color('info')
                            ->columnSpanFull(),
                    ]),

                Section::make('Application Access')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('department.dep_name')
                            ->label('Department'),
                        TextEntry::make('roles.display_name')
                            ->label('Roles')
                            ->badge()
                            ->separator(','),
                    ]),
            ]);
    }
}

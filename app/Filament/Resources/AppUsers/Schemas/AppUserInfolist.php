<?php

namespace App\Filament\Resources\AppUsers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AppUserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_fname')
                    ->label('First Name'),
                TextEntry::make('user_mname')
                    ->label('Middle Name'),
                TextEntry::make('user_lname')
                    ->label('Last Name'),
                TextEntry::make('user_email')
                    ->label('Email'),
                TextEntry::make('user_contact_no')
                    ->label('Contact #')
                    ->placeholder('-'),
                TextEntry::make('user_fb_profile_link')
                    ->label('Facebook Profile Link')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('department.dep_name')
                    ->label('Department'),
                TextEntry::make('roles.name')
                    ->label('Assigned Roles')
                    ->badge()
                    ->color('warning')
                    ->separator(',')
                    // Clean up underscores and capitalize for the view
                    ->formatStateUsing(fn(string $state): string => str($state)->replace('_', ' ')->title())
                    // If they have many roles, this ensures they wrap nicely in the infolist grid
                    ->columnSpanFull(),
            ]);
    }
}

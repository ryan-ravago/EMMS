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
                TextEntry::make('user_fname'),
                TextEntry::make('user_mname'),
                TextEntry::make('user_lname'),
                TextEntry::make('user_email'),
                TextEntry::make('user_contact_no')
                    ->placeholder('-'),
                TextEntry::make('user_fb_profile_link')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('user_dep_id')
                    ->numeric(),
            ]);
    }
}

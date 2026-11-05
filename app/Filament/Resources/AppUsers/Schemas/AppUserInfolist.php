<?php

namespace App\Filament\Resources\AppUsers\Schemas;

use App\Models\AppUser;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AppUserInfolist
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
                        ImageEntry::make('user_avatar')
                            ->label('Avatar')
                            ->circular()
                            // ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->user_fname . ' ' . Auth::user()->user_lname) . '&color=FFFFFF&background=03449d')
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->user_fname.' '.$record->user_lname).'&color=FFFFFF&background=03449d')
                            ->imageSize(120)
                            ->columnSpan(1),
                        TextEntry::make('department.dep_name')
                            ->label('Department'),
                        TextEntry::make('roles.display_name')
                            ->label('Roles')
                            ->badge()
                            ->separator(','),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                    ]),

                // Super admins only. It is read again from the database every minute, so it stays current
                // while the page is open (the page's own record is loaded once).
                Section::make('Activity')
                    ->key('presence')
                    ->poll('60s')
                    ->columns(3)
                    ->visible(fn (): bool => Auth::user()?->hasRole('super_admin') ?? false)
                    ->schema([
                        TextEntry::make('presence')
                            ->label('Status')
                            ->badge()
                            ->state(fn (AppUser $record): string => self::fresh($record)?->isOnline() ? 'Online' : 'Offline')
                            ->color(fn (string $state): string => $state === 'Online' ? 'success' : 'gray')
                            ->icon(fn (string $state): string => $state === 'Online' ? 'heroicon-o-signal' : 'heroicon-o-signal-slash'),
                        TextEntry::make('last_active_at')
                            ->label('Last active')
                            ->state(fn (AppUser $record) => self::fresh($record)?->last_active_at)
                            ->since()
                            ->dateTimeTooltip('M d, Y h:i A')
                            ->placeholder('Never'),
                        TextEntry::make('last_seen_at')
                            ->label('Last seen')
                            ->state(fn (AppUser $record) => self::fresh($record)?->last_seen_at)
                            ->since()
                            ->dateTimeTooltip('M d, Y h:i A')
                            ->placeholder('Never')
                            ->helperText('Includes the automatic refreshes of an open page.'),
                    ]),
            ]);
    }

    /**
     * The user's presence as it is in the database now, not as it was when the page opened.
     */
    private static function fresh(AppUser $user): ?AppUser
    {
        return AppUser::query()->find($user->getKey(), ['user_id', 'last_seen_at', 'last_active_at']);
    }
}

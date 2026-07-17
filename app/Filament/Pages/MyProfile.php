<?php

namespace App\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MyProfile extends Page implements HasInfolists
{
    use HasPageShield, InteractsWithInfolists;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'My Profile';

    protected string $view = 'filament.pages.my-profile';

    public function profileInfolist(Schema $schema): Schema
    {
        return $schema
            ->record(Auth::user())
            ->schema([
                Section::make('Profile Information')
                    ->description('Your personal and department details.')
                    ->icon('heroicon-o-user-circle')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('user_avatar')
                            ->label('Avatar')
                            ->circular()
                            ->size(120)
                            ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->user_fname . ' ' . Auth::user()->user_lname) . '&color=FFFFFF&background=03449d')
                            ->columnSpan(1),

                        Section::make()
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('user_fname')
                                    ->label('First Name'),
                                TextEntry::make('user_lname')
                                    ->label('Last Name'),
                                TextEntry::make('user_mname')
                                    ->label('Middle Name')
                                    ->placeholder('—'),
                                TextEntry::make('department.dep_name')
                                    ->label('Department')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('user_email')
                                    ->label('Email Address')
                                    ->icon('heroicon-m-envelope')
                                    ->iconColor('primary')
                                    ->copyable()
                                    ->copyMessage('Email copied'),
                                TextEntry::make('user_contact_no')
                                    ->label('Contact Number')
                                    ->icon('heroicon-m-phone')
                                    ->iconColor('success')
                                    ->placeholder('—'),
                                TextEntry::make('user_fb_profile_link')
                                    ->label('Facebook Profile')
                                    ->icon('heroicon-m-globe-alt')
                                    ->iconColor('info')
                                    ->url(fn($state) => $state)
                                    ->openUrlInNewTab()
                                    ->color('primary')
                                    ->placeholder('Not set')
                                    ->columnSpanFull(),
                                TextEntry::make('roles.display_name')
                                    ->label('Roles')
                                    ->badge()
                                    // ->color('warning')
                                    ->separator(',')
                                    ->wrap(),
                            ]),
                    ]),
            ]);
    }
}

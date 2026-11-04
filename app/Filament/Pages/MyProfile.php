<?php

namespace App\Filament\Pages;

use App\Services\Rfid\MovementNotifier;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MyProfile extends Page implements HasInfolists
{
    use HasPageShield, InteractsWithForms, InteractsWithInfolists;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'My Profile';

    protected string $view = 'filament.pages.my-profile';

    /** @var array{notify_asset_movements?: bool} */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'notify_asset_movements' => (bool) Auth::user()?->notify_asset_movements,
        ]);
    }

    /**
     * Only asset admins and asset managers are ever emailed about equipment and vehicle time out / time in.
     */
    public function canReceiveMovementEmails(): bool
    {
        return Auth::user()?->hasAnyRole(MovementNotifier::ROLES) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Toggle::make('notify_asset_movements')
                    ->label('Email me every time an equipment or service vehicle goes out or comes in')
                    ->helperText('You get one email per asset time out or time in, however many RFID tags were read. Saved as soon as you change it.')
                    ->live()
                    ->afterStateUpdated(function (bool $state): void {
                        // The switch is not shown to other roles, but never trust the client to respect that.
                        if (! $this->canReceiveMovementEmails()) {
                            return;
                        }

                        Auth::user()->update(['notify_asset_movements' => $state]);

                        Notification::make()
                            ->title($state ? 'Time in / out emails turned on' : 'Time in / out emails turned off')
                            ->success()
                            ->send();
                    }),
            ])
            ->statePath('data');
    }

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
                            ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->user_fname.' '.Auth::user()->user_lname).'&color=FFFFFF&background=03449d')
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
                                    ->url(fn ($state) => $state)
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

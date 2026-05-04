<?php

namespace App\Providers\Filament;

use Andreia\FilamentUiSwitcher\FilamentUiSwitcherPlugin;
use App\Filament\Helper\CustomLogin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\SapSyncManager;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->spa()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn() => new \Illuminate\Support\HtmlString(
                    app(\Illuminate\Foundation\Vite::class)(['resources/css/app.css', 'resources/js/app.js'])
                )
            )
            ->sidebarCollapsibleOnDesktop()
            ->login(CustomLogin::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('EMMS')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->renderHook(
                'panels::auth.login.form.after',
                fn() => view('auth.socialite.google')
            )
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup(function () {
                        // Check if user is logged in to avoid errors on the login page
                        // $user = auth()->user();

                        // if (!$user) {
                        //     return 'Super Admin'; // Default fallback
                        // }

                        // // Count the roles assigned to the current user
                        // $rolesCount = $user->roles()->count();

                        // return $rolesCount === 1
                        //     ? 'Roles and Permissions'
                        //     : 'Super Admin';
                        return 'Super Admin';
                    })
                    ->navigationSort(3)
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

<?php

namespace App\Providers\Filament;

use App\Filament\Helper\CustomLogin;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Http\Middleware\EnsureUserStillHasRole;
use App\Models\SiteSetting;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\GlobalSearchPosition;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Vite;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->passwordReset()
            ->globalSearch(position: GlobalSearchPosition::Sidebar)
            // ->spa(hasPrefetching: true)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                function () {
                    $font = rescue(
                        fn () => SiteSetting::instance()->site_font_family ?? 'Inter',
                        'Inter',
                        report: false
                    );

                    $encoded = str_replace(' ', '+', $font);

                    $vite = app(Vite::class)(['resources/css/app.css', 'resources/js/app.js']);

                    return new HtmlString(
                        $vite.
                            "<link href=\"https://fonts.googleapis.com/css2?family={$encoded}:wght@300;400;500;600;700&display=swap\" rel=\"stylesheet\">
                            <style>
                                body, html { font-family: '{$font}', sans-serif !important; }
                            </style>
                            "
                    );
                }
            )
            ->sidebarCollapsibleOnDesktop()
            // ->collapsibleNavigationGroups(false)
            ->login(CustomLogin::class)
            ->colors(function () {
                try {
                    $settings = SiteSetting::instance();
                    $primaryColor = match ($settings->site_primary_color) {
                        'red' => Color::Red,
                        'orange' => Color::Orange,
                        'amber' => Color::Amber,
                        'yellow' => Color::Yellow,
                        'lime' => Color::Lime,
                        'green' => Color::Green,
                        'emerald' => Color::Emerald,
                        'teal' => Color::Teal,
                        'cyan' => Color::Cyan,
                        'sky' => Color::Sky,
                        'blue' => Color::Blue,
                        'indigo' => Color::Indigo,
                        'violet' => Color::Violet,
                        'purple' => Color::Purple,
                        'fuchsia' => Color::Fuchsia,
                        'pink' => Color::Pink,
                        'rose' => Color::Rose,
                        default => Color::Amber,
                    };
                } catch (\Throwable) {
                    $primaryColor = Color::Amber;
                }

                return [
                    'danger' => Color::Red,
                    'gray' => Color::Zinc,
                    'info' => Color::Blue,
                    'primary' => $primaryColor,
                    'success' => Color::Green,
                    'warning' => Color::Yellow,
                ];
            })
            ->brandName(function () {
                try {
                    return SiteSetting::instance()->site_name;
                } catch (\Throwable) {
                    return 'EMMS';
                }
            })
            ->brandLogo(function () {
                $logo = SiteSetting::instance()->site_logo;

                return filled($logo)
                    ? Storage::disk('public')->url($logo)
                    : null;
            })
            ->brandLogoHeight(request()->is('login') ? '8rem' : '3rem')
            ->favicon(function () {
                try {
                    $favicon = SiteSetting::instance()->site_favicon;

                    return filled($favicon) ? Storage::disk('public')->url($favicon) : null;
                } catch (\Throwable) {
                    return null;
                }
            })
            // ->font(SiteSetting::instance()->site_font_family ?? 'Inter', provider: GoogleFontProvider::class)
            ->maxContentWidth(Width::SevenExtraLarge)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            // ->navigationGroups([
            //     NavigationGroup::make()
            //         ->label('Equipment Details')
            //         ->collapsed(false),
            // ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
                DashboardStatsOverview::class,
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
                EnsureUserStillHasRole::class,
                // 'throttle:filament',
            ])
            ->renderHook(
                'panels::auth.login.form.after',
                fn () => view('auth.socialite.google')
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
                        'lg' => 3,
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
                SimpleLightBoxPlugin::make(),
            ])
            ->navigationGroups([
                'Equipment',
                'Super Admin',
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

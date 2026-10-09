<?php

namespace App\Providers;

use App\Filament\Support\FileUploadDefaults;
use App\Filament\Support\ModalRecordNavigation;
use App\Models\AppUser;
use App\Models\Equipment;
use App\Models\Export;
use App\Observers\EquipmentObserver;
use App\Support\Activity\ActivityLogging;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export as FilamentExport;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FilamentExport::class, Export::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Equipment::observe(EquipmentObserver::class);

        // Readable activity log: record names, triggering action, bulk batches.
        ActivityLogging::register();

        // Filament's default page options include 'all', which renders every row
        // and freezes the browser on big tables. Cap it app-wide (tables and
        // relation managers); a table can still override with ->paginated().
        // On phones (< 640px) rows become cards (see resources/css/mobile-tables.css);
        // a table can opt out with ->stackedOnMobile(false).
        Table::configureUsing(
            fn (Table $table) => $table
                ->paginated([10, 25, 50, 100])
                ->defaultPaginationPageOption(25)
                ->stackedOnMobile()
        );

        // Every table column shows — when empty, and long text is cut to 50 characters
        // followed by "..." (the full text shows on hover). A column can still override
        // either with its own ->placeholder() / ->limit() / ->tooltip().
        Column::configureUsing(fn (Column $column) => $column->placeholder('—'));

        TextColumn::configureUsing(
            fn (TextColumn $column) => $column
                ->limit(50)
                ->tooltip(function (TextColumn $column): ?string {
                    $limit = $column->getCharacterLimit();
                    $state = $column->getState();

                    return $limit && is_string($state) && mb_strlen($state) > $limit ? $state : null;
                })
        );

        // Exported text that starts with = + - @ is read as a formula by Excel (CSV injection),
        // so prefix it with a quote. Filament only ships this from a later release; the macro
        // steps aside if the installed version already defines preventFormulaInjection().
        ExportColumn::macro('preventFormulaInjection', function (): ExportColumn {
            /** @var ExportColumn $this */
            return $this->formatStateUsing(
                fn (mixed $state): mixed => is_string($state) && $state !== '' && in_array($state[0], ['=', '+', '-', '@', "\t", "\r"], true)
                    ? "'".$state
                    : $state
            );
        });

        // Previous / Next buttons in every table "View" modal.
        ViewAction::configureUsing(fn (ViewAction $action) => ModalRecordNavigation::configure($action));

        // Every form upload refuses videos and optimizes images before the path reaches the database.
        FileUpload::configureUsing(fn (FileUpload $upload) => FileUploadDefaults::configure($upload));

        // Log (not throw) lazy-loaded relations so N+1 spots show up in storage/logs.
        Model::preventLazyLoading(! app()->isProduction());
        Model::handleLazyLoadingViolationUsing(
            fn (Model $model, string $relation) => logger()->warning(
                'N+1: lazy loading ['.$relation.'] on ['.$model::class.']'
            )
        );
        // URL::forceScheme('https');

        // RateLimiter::for('filament', function (Request $request) {
        //     if ($request->routeIs('filament.admin.auth.login') || $request->is('login*')) {
        //         return $this->loginLimit($request);
        //     }

        //     if ($this->isUploadRequest($request)) {
        //         return $this->uploadLimit($request);
        //     }

        //     if ($this->isExportRequest($request)) {
        //         return $this->exportLimit($request);
        //     }

        //     if ($this->isEmailTriggeringRequest($request)) {
        //         return $this->emailLimit($request);
        //     }

        //     if ($this->isMutationRequest($request)) {
        //         return $this->mutationLimit($request);
        //     }

        //     return $this->generalFilamentLimit($request);
        // });

        RateLimiter::for('auth', function (Request $request) {
            return $this->loginLimit($request);
        });

        // Backs `$middleware->throttleApi()` in bootstrap/app.php (used by the RFID log API).
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by('api:'.$this->rateLimitKey($request));
        });

        RateLimiter::for('google-auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // RateLimiter::for('uploads', function (Request $request) {
        //     return $this->uploadLimit($request);
        // });

        // RateLimiter::for('exports', function (Request $request) {
        //     return $this->exportLimit($request);
        // });

        // RateLimiter::for('emails', function (Request $request) {
        //     return $this->emailLimit($request);
        // });

        // RateLimiter::for('mutations', function (Request $request) {
        //     return $this->mutationLimit($request);
        // });

        Gate::define('viewPulse', function (AppUser $user) {
            return $user->hasRole('super_admin');
        });

        // Pulse's default resolver reads `name` / `email`, which AppUser doesn't have
        // (it uses user_fname / user_lname / user_email), so cards only showed "ID: 12".
        Pulse::user(fn (AppUser $user) => [
            'name' => $user->full_name ?: $user->user_email,
            'extra' => $user->user_email,
            'avatar' => $user->getFilamentAvatarUrl(),
        ]);
    }

    private function generalFilamentLimit(Request $request): Limit
    {
        return Limit::perMinute(60)->by('filament:'.$this->rateLimitKey($request));
    }

    private function loginLimit(Request $request): Limit
    {
        return Limit::perMinute(10)->by('login:'.$request->ip());
    }

    private function uploadLimit(Request $request): Limit
    {
        return Limit::perMinute(10)->by('uploads:'.$this->rateLimitKey($request));
    }

    private function exportLimit(Request $request): Limit
    {
        return Limit::perMinute(5)->by('exports:'.$this->rateLimitKey($request));
    }

    private function emailLimit(Request $request): Limit
    {
        return Limit::perMinute(5)->by('emails:'.$this->rateLimitKey($request));
    }

    private function mutationLimit(Request $request): Limit
    {
        return Limit::perMinute(30)->by('mutations:'.$this->rateLimitKey($request));
    }

    private function rateLimitKey(Request $request): string
    {
        return (string) ($request->user()?->getKey() ?: $request->ip());
    }

    private function isUploadRequest(Request $request): bool
    {
        return $request->routeIs('livewire.upload-file')
            || $request->is('livewire*/upload-file');
    }

    private function isExportRequest(Request $request): bool
    {
        return $request->routeIs('filament.exports.*', 'filament.imports.failed-rows.*')
            || $request->is('filament/exports/*')
            || $request->is('filament/imports/*/failed-rows/download');
    }

    private function isEmailTriggeringRequest(Request $request): bool
    {
        if (! $request->isMethod('POST')) {
            return false;
        }

        $payload = str($request->getContent())->lower();

        return $payload->contains([
            'create-inspection',
            'create-requestor-work-order',
            'create-work-order',
            'makeworkorder',
            'disregard',
            'requestapproval',
            'approve',
            'reject',
            'assign',
            'complete',
            'cancel',
        ]);
    }

    private function isMutationRequest(Request $request): bool
    {
        return $request->isMethod('POST')
            || $request->isMethod('PUT')
            || $request->isMethod('PATCH')
            || $request->isMethod('DELETE');
    }
}

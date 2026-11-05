<?php

namespace App\Http\Middleware;

use App\Models\AppUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\ImpersonateManager;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Remembers when a signed-in user was last seen and last active, so super admins can tell who is online.
 *
 *  - last_seen_at: any request, including the refreshes an open page makes on its own (notifications, tables,
 *    widgets). It is what "online" is based on.
 *  - last_active_at: a person opening or using a page. The automatic refreshes are not counted, so a tab left
 *    open on a desk does not look like someone working.
 *
 * To keep the database quiet it writes at most once per config('presence.write_every_seconds') per user, with a
 * single UPDATE that skips model events (and so the activity log). Someone impersonating a user does not make that
 * user look active. A failure here is reported but never stops the request.
 */
class TrackUserActivity
{
    /** Livewire calls that a page makes by itself on a timer (wire:poll, chart updates, partial re-renders). */
    private const BACKGROUND_CALLS = ['$refresh', 'partiallyRenderSchemaComponent', 'updateChartData'];

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->record();
        } catch (Throwable $exception) {
            report($exception);
        }

        return $next($request);
    }

    private function record(): void
    {
        $user = Auth::user();

        if (! $user instanceof AppUser || app(ImpersonateManager::class)->isImpersonating()) {
            return;
        }

        $now = now();
        $throttle = $now->copy()->subSeconds(max(1, (int) config('presence.write_every_seconds')));
        $changes = [];

        if ($user->last_seen_at === null || $user->last_seen_at->lte($throttle)) {
            $changes['last_seen_at'] = $now;
        }

        if (! $this->isBackgroundRefresh() && ($user->last_active_at === null || $user->last_active_at->lte($throttle))) {
            $changes['last_active_at'] = $now;
        }

        if ($changes !== []) {
            // The query builder, not the model: no events, no activity log entry for every minute of someone's day.
            AppUser::query()->whereKey($user->getKey())->update($changes);
        }
    }

    /**
     * A Livewire request that only carries timer-driven refreshes: no changed values and nothing but the known
     * background calls. Anything else (a click, typing, a filter, an action, a page load) is a person.
     */
    private function isBackgroundRefresh(): bool
    {
        // For a Livewire request this middleware may run against a copy of the page's own request, so the
        // payload is read from the real, current request.
        $components = request()->input('components');

        if (! is_array($components) || $components === []) {
            return false;
        }

        foreach ($components as $component) {
            if (! is_array($component) || ! empty($component['updates'])) {
                return false;
            }

            $calls = $component['calls'] ?? [];

            if (! is_array($calls) || $calls === []) {
                return false;
            }

            foreach ($calls as $call) {
                if (! is_array($call) || ! in_array($call['method'] ?? null, self::BACKGROUND_CALLS, true)) {
                    return false;
                }
            }
        }

        return true;
    }
}

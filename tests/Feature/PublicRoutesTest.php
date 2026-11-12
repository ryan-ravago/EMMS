<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    public function test_the_database_connection_debug_route_is_gone(): void
    {
        $this->assertFalse(
            collect(Route::getRoutes())->contains(fn ($route): bool => $route->uri() === 'test-aims-db'),
            'A route that tests a database connection must not be reachable without signing in.',
        );
    }

    public function test_routes_defined_in_the_web_file_are_only_the_known_public_ones(): void
    {
        $webFileRoutes = collect(Route::getRoutes())
            ->filter(fn ($route): bool => in_array($route->getName(), ['pwa.manifest', 'auth.google', 'auth.google.callback'], true))
            ->map(fn ($route): string => $route->uri())
            ->sort()
            ->values()
            ->all();

        $this->assertSame(['auth/google', 'auth/google/callback', 'manifest.webmanifest'], $webFileRoutes);
    }
}

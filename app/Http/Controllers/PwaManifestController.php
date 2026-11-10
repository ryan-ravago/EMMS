<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    /**
     * The colour of the browser bar and splash screen of the installed app; it matches the icons in public/pwa.
     */
    public const THEME_COLOR = '#03449d';

    /**
     * The web app manifest: what a phone or browser needs to offer "Install app". The name follows Site Settings.
     */
    public function __invoke(): JsonResponse
    {
        $name = rescue(fn () => SiteSetting::instance()->site_name, 'EMMS', report: false);
        $name = is_string($name) && trim($name) !== '' ? trim($name) : 'EMMS';

        return response()
            ->json([
                'id' => '/',
                'name' => $name,
                'short_name' => mb_substr($name, 0, 12),
                'start_url' => '/',
                'scope' => '/',
                'display' => 'standalone',
                'background_color' => '#ffffff',
                'theme_color' => self::THEME_COLOR,
                'icons' => [
                    ['src' => '/pwa/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                    ['src' => '/pwa/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                    ['src' => '/pwa/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
                    ['src' => '/pwa/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
                ],
            ])
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}

<?php

namespace Tests\Feature;

use App\Http\Controllers\PwaManifestController;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PwaTest extends TestCase
{
    private function siteSettingsTable(): void
    {
        config(['activitylog.enabled' => false]);

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('site_favicon')->nullable();
            $table->string('site_primary_color')->nullable();
            $table->string('site_font_family')->nullable();
            $table->timestamps();
        });
    }

    public function test_the_manifest_is_public_and_describes_an_installable_app(): void
    {
        $this->siteSettingsTable();

        $response = $this->get('/manifest.webmanifest');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertJsonPath('name', 'EMMS')
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('start_url', '/')
            ->assertJsonPath('scope', '/')
            ->assertJsonPath('theme_color', PwaManifestController::THEME_COLOR);

        $sizes = collect($response->json('icons'))->pluck('sizes')->unique()->sort()->values()->all();
        $this->assertSame(['192x192', '512x512'], $sizes);
        $this->assertContains('maskable', collect($response->json('icons'))->pluck('purpose')->all());
    }

    public function test_the_manifest_name_follows_the_site_settings(): void
    {
        $this->siteSettingsTable();
        DB::table('site_settings')->insert(['site_name' => 'Ravago Fleet', 'site_primary_color' => 'amber']);

        $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertJsonPath('name', 'Ravago Fleet')
            ->assertJsonPath('short_name', 'Ravago Fleet');
    }

    public function test_the_manifest_still_works_when_the_settings_cannot_be_read(): void
    {
        // No site_settings table at all, as on a fresh deploy before migrating.
        $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertJsonPath('name', 'EMMS');
    }

    public function test_every_icon_in_the_manifest_exists_with_the_declared_size(): void
    {
        $this->siteSettingsTable();

        foreach ($this->get('/manifest.webmanifest')->json('icons') as $icon) {
            $path = public_path(ltrim($icon['src'], '/'));

            $this->assertFileExists($path);
            [$width, $height] = getimagesize($path);
            $this->assertSame($icon['sizes'], "{$width}x{$height}");
        }
    }

    public function test_the_service_worker_exists_and_never_answers_a_request_itself(): void
    {
        $worker = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString("addEventListener('fetch'", $worker);
        // Answering or caching requests could show stale asset statuses or break Livewire calls.
        $this->assertStringNotContainsString('respondWith(', $worker);
        $this->assertStringNotContainsString('caches.', $worker);
    }

    public function test_the_panel_head_links_the_manifest_and_registers_the_service_worker(): void
    {
        $this->siteSettingsTable();
        Filament::setCurrentPanel('admin');
        // The panel registers its render hooks when it boots, which a real request does first.
        Filament::getCurrentPanel()->boot();

        $head = (string) FilamentView::renderHook(PanelsRenderHook::HEAD_END);

        $this->assertStringContainsString('<link rel="manifest" href="/manifest.webmanifest">', $head);
        $this->assertStringContainsString('name="theme-color"', $head);
        $this->assertStringContainsString('navigator.serviceWorker.register("/sw.js"', $head);
    }
}

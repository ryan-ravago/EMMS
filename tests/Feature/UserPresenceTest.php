<?php

namespace Tests\Feature;

use App\Filament\Resources\AppUsers\Pages\ListAppUsers;
use App\Filament\Resources\AppUsers\Pages\ViewAppUser;
use App\Http\Middleware\TrackUserActivity;
use App\Models\AppUser;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use STS\FilamentImpersonate\ImpersonateManager;
use Tests\TestCase;

class UserPresenceTest extends TestCase
{
    private AppUser $superAdmin;

    private AppUser $manager;

    private AppUser $worker;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false, 'presence.online_seconds' => 120, 'presence.write_every_seconds' => 30]);
        Carbon::setTestNow('2026-11-05 12:00:00');

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();
        Schema::table('roles', fn (Blueprint $table) => $table->string('display_name')->nullable());

        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code')->nullable();
            $table->string('dep_name')->nullable();
        });

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_mname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_avatar')->nullable();
            $table->string('user_contact_no')->nullable();
            $table->string('user_fb_profile_link')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('notify_asset_movements')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Filament::setCurrentPanel('admin');

        foreach (['super_admin', 'manager', 'worker'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // Set directly: how the role model treats display_name depends on which role class other tests registered.
        DB::table('roles')->update(['display_name' => DB::raw('name')]);

        // Super admin has every permission in this app; the manager may open the users pages but is not a super admin.
        foreach (['ViewAny:AppUserResource', 'View:AppUserResource'] as $permission) {
            $permission = Permission::create(['name' => $permission, 'guard_name' => 'web']);
            Role::findByName('super_admin')->givePermissionTo($permission);
            Role::findByName('manager')->givePermissionTo($permission);
        }

        $this->superAdmin = $this->user('Sam', 'Super', 'super_admin');
        $this->manager = $this->user('Mia', 'Manager', 'manager');
        $this->worker = $this->user('Walt', 'Worker', 'worker');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function user(string $first, string $last, string $role): AppUser
    {
        $user = AppUser::create([
            'user_fname' => $first, 'user_lname' => $last, 'user_email' => strtolower("{$first}.{$last}@example.test"),
            'is_active' => true,
        ])->refresh();
        $user->assignRole($role);

        return $user;
    }

    /**
     * A request as the middleware sees it: the page's own request, and the real Livewire request when there is one.
     *
     * @param  list<array{updates?: array<string, mixed>, calls?: list<array{method: string}>}>|null  $components
     */
    private function pass(?array $components = null): void
    {
        $request = $components === null
            ? Request::create('/admin/users')
            : Request::create('/livewire/update', 'POST', ['components' => $components]);
        app()->instance('request', $request);

        (new TrackUserActivity)->handle($request, fn () => response('ok'));
    }

    private function poll(): array
    {
        return [['snapshot' => '{}', 'updates' => [], 'calls' => [['method' => '$refresh', 'params' => []]]]];
    }

    private function seen(AppUser $user): ?string
    {
        return $user->fresh()->getRawOriginal('last_seen_at');
    }

    private function active(AppUser $user): ?string
    {
        return $user->fresh()->getRawOriginal('last_active_at');
    }

    // ---- recording ----------------------------------------------------------------------------------------

    public function test_opening_a_page_marks_the_user_as_seen_and_active(): void
    {
        $this->actingAs($this->worker);

        $this->pass();

        $this->assertSame('2026-11-05 12:00:00', $this->seen($this->worker));
        $this->assertSame('2026-11-05 12:00:00', $this->active($this->worker));
    }

    public function test_the_automatic_refreshes_of_an_open_page_keep_the_user_online_but_not_active(): void
    {
        $this->actingAs($this->worker);
        $this->pass();

        Carbon::setTestNow('2026-11-05 12:10:00');
        $this->actingAs($this->worker->fresh());
        $this->pass($this->poll());

        $this->assertSame('2026-11-05 12:10:00', $this->seen($this->worker));
        $this->assertSame('2026-11-05 12:00:00', $this->active($this->worker));
    }

    public function test_using_a_page_counts_as_activity_but_a_poll_mixed_with_a_real_call_does_too(): void
    {
        $this->actingAs($this->worker);
        $this->pass();

        // Typing in a field sends changed values.
        Carbon::setTestNow('2026-11-05 12:05:00');
        $this->actingAs($this->worker->fresh());
        $this->pass([['snapshot' => '{}', 'updates' => ['tableSearch' => 'crane'], 'calls' => [['method' => '$refresh']]]]);
        $this->assertSame('2026-11-05 12:05:00', $this->active($this->worker));

        // Clicking something calls a method.
        Carbon::setTestNow('2026-11-05 12:20:00');
        $this->actingAs($this->worker->fresh());
        $this->pass([['snapshot' => '{}', 'updates' => [], 'calls' => [['method' => '$refresh'], ['method' => 'mountAction']]]]);
        $this->assertSame('2026-11-05 12:20:00', $this->active($this->worker));
    }

    public function test_the_table_and_infolist_refreshes_and_chart_updates_are_background_work(): void
    {
        $this->actingAs($this->worker);
        $this->pass();

        Carbon::setTestNow('2026-11-05 12:30:00');
        $this->actingAs($this->worker->fresh());
        $this->pass([
            ['snapshot' => '{}', 'updates' => [], 'calls' => [['method' => 'partiallyRenderSchemaComponent']]],
            ['snapshot' => '{}', 'updates' => [], 'calls' => [['method' => 'updateChartData']]],
        ]);

        $this->assertSame('2026-11-05 12:30:00', $this->seen($this->worker));
        $this->assertSame('2026-11-05 12:00:00', $this->active($this->worker));
    }

    public function test_the_database_is_written_at_most_once_per_interval(): void
    {
        $this->actingAs($this->worker);
        $this->pass();

        // Ten seconds later: nothing to write.
        Carbon::setTestNow('2026-11-05 12:00:10');
        $this->actingAs($this->worker->fresh());
        $this->pass();
        $this->assertSame('2026-11-05 12:00:00', $this->seen($this->worker));

        // Past the interval: written again.
        Carbon::setTestNow('2026-11-05 12:00:31');
        $this->actingAs($this->worker->fresh());
        $this->pass();
        $this->assertSame('2026-11-05 12:00:31', $this->seen($this->worker));
    }

    public function test_someone_impersonating_a_user_does_not_make_that_user_look_active(): void
    {
        $this->actingAs($this->worker);
        session()->put(ImpersonateManager::SESSION_KEY, $this->superAdmin->getKey());

        $this->pass();

        $this->assertNull($this->seen($this->worker));
        $this->assertNull($this->active($this->worker));
    }

    public function test_a_request_without_a_signed_in_user_writes_nothing_and_never_fails(): void
    {
        $this->pass();

        $this->assertNull($this->seen($this->worker));
    }

    public function test_signing_out_shows_the_user_as_offline_at_once(): void
    {
        $this->actingAs($this->worker);
        $this->pass();
        $this->assertTrue($this->worker->fresh()->isOnline());

        Event::dispatch(new Logout('web', $this->worker));

        $this->assertNull($this->seen($this->worker));
        $this->assertFalse($this->worker->fresh()->isOnline());
    }

    public function test_every_panel_page_runs_it_after_the_sign_in_check(): void
    {
        $middleware = Route::getRoutes()->getByName('filament.admin.resources.app-users.index')->gatherMiddleware();

        $this->assertContains(TrackUserActivity::class, $middleware);
        $this->assertGreaterThan(
            array_search(Authenticate::class, $middleware, true),
            array_search(TrackUserActivity::class, $middleware, true),
            'it must run once the user is signed in',
        );
    }

    public function test_livewire_applies_it_to_the_automatic_refreshes_too(): void
    {
        $this->assertContains(TrackUserActivity::class, Livewire::getPersistentMiddleware());
    }

    public function test_the_presence_columns_are_not_written_to_the_activity_log(): void
    {
        $excluded = (new \ReflectionProperty(AppUser::class, 'activityExclude'))->getValue($this->worker);

        $this->assertContains('last_seen_at', $excluded);
        $this->assertContains('last_active_at', $excluded);
    }

    // ---- online or offline --------------------------------------------------------------------------------

    public function test_a_user_is_online_while_the_last_request_is_recent(): void
    {
        $user = $this->worker;

        $this->assertFalse($user->isOnline(), 'never seen');

        $user->forceFill(['last_seen_at' => now()->subSeconds(90)])->save();
        $this->assertTrue($user->isOnline());

        $user->forceFill(['last_seen_at' => now()->subSeconds(121)])->save();
        $this->assertFalse($user->isOnline());

        $user->forceFill(['last_seen_at' => now()->subSeconds(90)])->save();
        $this->assertSame([$user->getKey()], AppUser::query()->online()->pluck('user_id')->all());
    }

    // ---- the pages ------------------------------------------------------------------------------------------

    public function test_a_super_admin_sees_who_is_online_and_when_each_was_last_active(): void
    {
        $this->worker->forceFill(['last_seen_at' => now()->subSeconds(30), 'last_active_at' => now()->subMinutes(3)])->save();
        $this->manager->forceFill(['last_seen_at' => now()->subHours(2), 'last_active_at' => now()->subHours(2)])->save();
        $this->actingAs($this->superAdmin);

        Livewire::test(ListAppUsers::class)
            ->loadTable()
            ->assertTableColumnVisible('presence')
            ->assertTableColumnVisible('last_active_at')
            ->assertTableColumnStateSet('presence', 'Online', $this->worker)
            ->assertTableColumnStateSet('presence', 'Offline', $this->manager)
            ->assertTableColumnStateSet('presence', 'Offline', $this->superAdmin)
            ->assertSee('3 minutes ago')
            ->assertSee('Never');
    }

    public function test_the_users_table_refreshes_every_minute(): void
    {
        $this->actingAs($this->superAdmin);

        $table = Livewire::test(ListAppUsers::class)->loadTable()->instance()->getTable();

        $this->assertSame('60s', $table->getPollingInterval());
    }

    public function test_online_users_can_be_listed_with_one_switch(): void
    {
        $this->worker->forceFill(['last_seen_at' => now()->subSeconds(30)])->save();
        $this->actingAs($this->superAdmin);

        Livewire::test(ListAppUsers::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$this->worker, $this->manager])
            ->filterTable('online', true)
            ->assertCanSeeTableRecords([$this->worker])
            ->assertCanNotSeeTableRecords([$this->manager]);
    }

    public function test_others_do_not_see_presence_in_the_table_or_the_user_page(): void
    {
        $this->worker->forceFill(['last_seen_at' => now()->subSeconds(30), 'last_active_at' => now()->subMinutes(3)])->save();
        $this->actingAs($this->manager);

        Livewire::test(ListAppUsers::class)
            ->loadTable()
            ->assertTableColumnHidden('presence')
            ->assertTableColumnHidden('last_active_at')
            ->assertDontSee('Online')
            ->assertDontSee('minutes ago');

        Livewire::test(ViewAppUser::class, ['record' => $this->worker->getKey()])
            ->assertSuccessful()
            ->assertDontSee('Activity')
            ->assertDontSee('Online')
            ->assertDontSee('Last active');
    }

    public function test_the_user_page_shows_the_status_and_keeps_it_current(): void
    {
        $this->worker->forceFill(['last_seen_at' => now()->subSeconds(30), 'last_active_at' => now()->subMinutes(3)])->save();
        $this->actingAs($this->superAdmin);

        $page = Livewire::test(ViewAppUser::class, ['record' => $this->worker->getKey()])
            ->assertSuccessful()
            ->assertSee('Activity')
            ->assertSee('Online')
            ->assertSee('3 minutes ago');

        // Two minutes pass without a request from them; the page, left open, reads the database again on its next refresh.
        Carbon::setTestNow('2026-11-05 12:05:00');
        $page->call('$refresh')
            ->assertSee('Offline')
            ->assertDontSee('Online');

        // And the page asks for that refresh every minute.
        $this->assertStringContainsString('wire:poll.60s', $page->html());
    }
}

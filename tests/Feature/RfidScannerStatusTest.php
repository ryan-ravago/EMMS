<?php

namespace Tests\Feature;

use App\Filament\Resources\RfidScannerStatuses\Pages\ListRfidScannerStatuses;
use App\Filament\Resources\RfidScannerStatuses\Pages\ViewRfidScannerStatus;
use App\Filament\Resources\RfidScannerStatuses\RelationManagers\LogsRelationManager;
use App\Filament\Resources\RfidScannerStatuses\RfidScannerStatusResource;
use App\Filament\Widgets\RfidScannerStatusStats;
use App\Mail\RfidHealthAlertMail;
use App\Models\Location;
use App\Models\RfidScannerStatus;
use App\Models\RfidScannerStatusLog;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RfidScannerStatusTest extends AssetMovementTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-03 10:00:00');
        Cache::flush();
        config([
            'rfid.scanner_stale_minutes' => 15,
            'rfid.scanner_log_retention_days' => 30,
            'rfid.alerts.enabled' => true,
            'rfid.alerts.emails' => 'it@example.test',
            'rfid.alerts.scanner_inactive_minutes' => 5,
            'rfid.alerts.reminder_hours' => 6,
            'rfid.alerts.window_start' => '00:00',
            'rfid.alerts.window_end' => '23:59',
            'rfid.alerts.days' => '1,2,3,4,5,6,7',
        ]);
    }

    private function url(?int $yard = null): string
    {
        return '/v1/'.($yard ?? $this->yard->id).'/rfid-scanner-status';
    }

    private function asReader(array $abilities = ['rfid:write']): void
    {
        Sanctum::actingAs($this->makeUser(), $abilities);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function report(array $overrides = [], ?int $yard = null)
    {
        return $this->postJson($this->url($yard), $overrides + ['scannerName' => 'OUT', 'status' => 1, 'timestamp' => '2026-11-03 10:00:00']);
    }

    private function scanner(string $name = 'OUT'): RfidScannerStatus
    {
        return RfidScannerStatus::query()->where('scanner_name', $name)->firstOrFail();
    }

    /**
     * A scanner row as the API would have left it.
     *
     * @param  array<string, mixed>  $extra
     */
    private function makeScanner(string $name, bool $active, string $changedAt, string $lastSeen, array $extra = []): RfidScannerStatus
    {
        $id = DB::table('rfid_scanner_statuses')->insertGetId($extra + [
            'yard_id' => $this->yard->id,
            'scanner_name' => $name,
            'is_active' => $active,
            'reported_at' => $changedAt,
            'status_changed_at' => $changedAt,
            'last_seen_at' => $lastSeen,
        ]);

        return RfidScannerStatus::findOrFail($id);
    }

    // ---- API ---------------------------------------------------------------------------------------------

    public function test_the_first_report_creates_the_scanner_and_its_history(): void
    {
        $this->asReader();

        $this->report(['status' => 1])
            ->assertOk()
            ->assertExactJson(['received' => 1, 'applied' => true, 'changed' => true]);

        $scanner = $this->scanner();
        $this->assertTrue($scanner->is_active);
        $this->assertSame($this->yard->id, $scanner->yard_id);
        $this->assertSame(1, RfidScannerStatusLog::count());
    }

    public function test_a_repeated_report_only_keeps_the_scanner_alive_and_writes_no_history(): void
    {
        $this->asReader();
        $this->report(['timestamp' => '2026-11-03 09:50:00'])->assertOk();
        DB::table('rfid_scanner_statuses')->update(['last_seen_at' => '2026-11-03 09:50:00']);

        $this->report(['timestamp' => '2026-11-03 09:55:00'])
            ->assertOk()
            ->assertExactJson(['received' => 1, 'applied' => true, 'changed' => false]);

        $this->assertSame(1, RfidScannerStatus::count());
        $this->assertSame(1, RfidScannerStatusLog::count());
        $this->assertSame('2026-11-03 09:55:00', $this->scanner()->reported_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-03 09:50:00', $this->scanner()->status_changed_at->format('Y-m-d H:i:s'));
        $this->assertNotSame('2026-11-03 09:50:00', $this->scanner()->getRawOriginal('last_seen_at'));
    }

    public function test_a_change_of_status_is_recorded_in_the_history(): void
    {
        $this->asReader();
        $this->report(['status' => 1, 'timestamp' => '2026-11-03 09:00:00'])->assertOk();

        $this->report(['status' => 0, 'timestamp' => '2026-11-03 09:30:00'])
            ->assertOk()
            ->assertJsonPath('changed', true);

        $scanner = $this->scanner();
        $this->assertFalse($scanner->is_active);
        $this->assertSame('2026-11-03 09:30:00', $scanner->status_changed_at->format('Y-m-d H:i:s'));
        $this->assertSame([true, false], RfidScannerStatusLog::orderBy('id')->pluck('is_active')->all());
    }

    public function test_an_older_report_than_the_one_held_is_not_applied(): void
    {
        $this->asReader();
        $this->report(['status' => 0, 'timestamp' => '2026-11-03 09:30:00'])->assertOk();

        $this->report(['status' => 1, 'timestamp' => '2026-11-03 09:00:00'])
            ->assertOk()
            ->assertExactJson(['received' => 1, 'applied' => false, 'changed' => false]);

        $this->assertFalse($this->scanner()->is_active);
        $this->assertSame(1, RfidScannerStatusLog::count());
    }

    public function test_the_scanner_name_is_case_insensitive_and_scanners_and_yards_are_kept_apart(): void
    {
        $this->asReader();
        $otherYard = Location::create(['name' => 'Other Yard']);

        $this->report(['scannerName' => 'in'])->assertOk();
        $this->report(['scannerName' => 'OUT'])->assertOk();
        $this->report(['scannerName' => 'In'], $otherYard->id)->assertOk();

        $this->assertSame(3, RfidScannerStatus::count());
        $this->assertSame(['IN', 'IN', 'OUT'], RfidScannerStatus::orderBy('scanner_name')->pluck('scanner_name')->all());
    }

    public function test_the_status_may_be_sent_as_a_boolean_or_text(): void
    {
        $this->asReader();

        $this->report(['status' => true, 'scannerName' => 'IN'])->assertOk();
        $this->report(['status' => '0', 'scannerName' => 'OUT'])->assertOk();

        $this->assertTrue($this->scanner('IN')->is_active);
        $this->assertFalse($this->scanner('OUT')->is_active);
    }

    public function test_bad_requests_are_rejected_and_nothing_is_stored(): void
    {
        $this->asReader();

        $this->report(['scannerName' => 'SIDE'])->assertStatus(422)->assertJsonValidationErrors('scannerName');
        $this->report(['scannerName' => ''])->assertStatus(422);
        $this->report(['status' => 2])->assertStatus(422)->assertJsonValidationErrors('status');
        $this->report(['status' => 'yes'])->assertStatus(422);
        $this->report(['timestamp' => 'yesterday'])->assertStatus(422)->assertJsonValidationErrors('timestamp');
        $this->report(['timestamp' => '2026-11-03 10:06:00'])->assertStatus(422)->assertJsonValidationErrors('timestamp');
        $this->postJson($this->url(), [])->assertStatus(422);

        $this->assertSame(0, RfidScannerStatus::count());
        $this->assertSame(0, RfidScannerStatusLog::count());
    }

    public function test_a_timestamp_a_little_ahead_is_accepted(): void
    {
        $this->asReader();

        $this->report(['timestamp' => '2026-11-03 10:04:00'])->assertOk();
    }

    public function test_the_request_must_be_json_small_and_for_a_known_yard(): void
    {
        $this->asReader();

        $this->post($this->url(), ['scannerName' => 'IN'])->assertStatus(415);
        $this->report([], 99999)->assertNotFound();
        $this->report(['padding' => str_repeat('x', 5000)])->assertStatus(413);
    }

    public function test_it_needs_the_reader_token_with_the_write_ability(): void
    {
        $this->report()->assertUnauthorized();

        $this->asReader(['something:else']);
        $this->report()->assertForbidden();

        $this->assertSame(0, RfidScannerStatus::count());
    }

    public function test_a_status_report_counts_as_the_reader_server_contacting_emms(): void
    {
        $service = $this->makeUser('RFID', 'Integration');
        $plain = $service->createToken(config('rfid.token_name'), ['rfid:write'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$plain)
            ->postJson($this->url(), ['scannerName' => 'IN', 'status' => 1, 'timestamp' => '2026-11-03 10:00:00'])
            ->assertOk();

        $this->assertNotNull($service->tokens()->first()->last_used_at);
        $this->assertSame($service->tokens()->first()->id, RfidScannerStatusLog::sole()->token_id);
    }

    // ---- state -------------------------------------------------------------------------------------------

    /**
     * The "last report" age is measured on the database clock, which test time does not move: build these times
     * from the database's own clock and line the application clock up with it.
     */
    private function ago(int $minutes): string
    {
        return $this->databaseNow()->subMinutes($minutes)->toDateTimeString();
    }

    private function databaseNow(): Carbon
    {
        return Carbon::parse(DB::selectOne('select CURRENT_TIMESTAMP as now')->now);
    }

    private function useDatabaseClock(): void
    {
        Carbon::setTestNow($this->databaseNow());
    }

    public function test_a_scanner_is_active_inactive_or_has_no_report(): void
    {
        $fresh = $this->ago(2);
        $stale = $this->ago(20);

        $this->assertSame(RfidScannerStatus::ACTIVE, $this->makeScanner('IN', true, $fresh, $fresh)->state());
        $this->assertSame(RfidScannerStatus::INACTIVE, $this->makeScanner('OUT', false, $fresh, $fresh)->state());
        $this->assertSame(RfidScannerStatus::NO_REPORT, $this->makeScanner('X', true, $stale, $stale)->state());
        $this->assertSame(RfidScannerStatus::NO_REPORT, $this->makeScanner('Y', false, $stale, $stale)->state());
    }

    // ---- alerts ------------------------------------------------------------------------------------------

    private function check(): void
    {
        $this->artisan('rfid:check-scanners')->assertSuccessful();
    }

    public function test_nothing_is_sent_when_alerts_are_off(): void
    {
        $this->useDatabaseClock();
        Mail::fake();
        config(['rfid.alerts.enabled' => false]);
        $this->makeScanner('OUT', false, $this->ago(120), $this->ago(1));

        $this->check();

        Mail::assertNothingQueued();
        Mail::assertNothingQueued();
    }

    public function test_a_scanner_that_just_went_inactive_is_not_announced_yet(): void
    {
        $this->useDatabaseClock();
        Mail::fake();
        $this->makeScanner('OUT', false, $this->ago(3), $this->ago(1));

        $this->check();

        Mail::assertNothingQueued();
    }

    public function test_an_inactive_scanner_is_announced_once_then_reminded_then_recovery_is_sent(): void
    {
        $this->useDatabaseClock();
        Mail::fake();
        $scanner = $this->makeScanner('OUT', false, $this->ago(60), $this->ago(1));

        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 1);
        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->kind === RfidHealthAlertMail::PROBLEM
            && str_contains($mail->headline, 'Valenzuela Yard · OUT')
            && $mail->hasTo('it@example.test'));

        // Same problem a few minutes later: no second email.
        Carbon::setTestNow(now()->addMinutes(5));
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 1);

        // Reminder after the reminder period (only the application clock moves; the report stays fresh).
        Carbon::setTestNow(now()->addHours(6));
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->kind === RfidHealthAlertMail::REMINDER);

        // The reader server reports it active again.
        DB::table('rfid_scanner_statuses')->update(['is_active' => true, 'status_changed_at' => now()->toDateTimeString(), 'last_seen_at' => $this->ago(0)]);
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->kind === RfidHealthAlertMail::RECOVERED);

        $scanner->refresh();
        $this->assertNull($scanner->alert_cause);
        $this->assertNull($scanner->last_notified_at);
    }

    public function test_a_scanner_that_stops_reporting_is_announced(): void
    {
        $this->useDatabaseClock();
        Mail::fake();
        $this->makeScanner('IN', true, $this->ago(120), $this->ago(30));

        $this->check();

        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->kind === RfidHealthAlertMail::PROBLEM
            && str_contains($mail->headline, 'No status reports'));
    }

    public function test_a_working_scanner_sends_nothing_and_a_recovery_is_only_sent_if_it_was_announced(): void
    {
        $this->useDatabaseClock();
        Mail::fake();
        $this->makeScanner('IN', true, $this->ago(120), $this->ago(1));
        // An open episode nobody was told about (for example outside the alert window).
        $this->makeScanner('OUT', true, $this->ago(120), $this->ago(1), ['alert_cause' => 'inactive', 'alert_since' => $this->ago(180)]);

        $this->check();

        Mail::assertNothingQueued();
        $this->assertNull($this->scanner('OUT')->alert_cause);
    }

    public function test_no_alert_is_sent_outside_the_alert_window(): void
    {
        $this->useDatabaseClock();
        Mail::fake();
        $outside = now()->addHours(3)->format('H:i');
        config(['rfid.alerts.window_start' => $outside, 'rfid.alerts.window_end' => $outside]);
        $this->makeScanner('OUT', false, $this->ago(60), $this->ago(1));

        $this->check();

        Mail::assertNothingQueued();
    }

    // ---- history retention ---------------------------------------------------------------------------------

    public function test_status_history_is_pruned_after_the_retention_period(): void
    {
        config(['rfid.scanner_log_retention_days' => 7]);
        DB::table('rfid_scanner_status_logs')->insert([
            ['yard_id' => $this->yard->id, 'scanner_name' => 'IN', 'is_active' => 1, 'reported_at' => '2026-10-20 08:00:00', 'received_at' => '2026-10-20 08:00:00'],
            ['yard_id' => $this->yard->id, 'scanner_name' => 'IN', 'is_active' => 0, 'reported_at' => '2026-11-01 08:00:00', 'received_at' => '2026-11-01 08:00:00'],
        ]);

        $this->artisan('model:prune', ['--model' => [RfidScannerStatusLog::class]])->assertSuccessful();

        $this->assertSame(1, RfidScannerStatusLog::count());
        $this->assertFalse(RfidScannerStatusLog::sole()->is_active);
    }

    // ---- screen ------------------------------------------------------------------------------------------

    private function signInAs(string $role, array $permissions = []): void
    {
        Role::findOrCreate('super_admin', 'web');
        Role::findOrCreate($role, 'web');
        Filament::setCurrentPanel('admin');

        $user = $this->makeUser();
        $user->assignRole($role);

        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $this->actingAs($user);
    }

    public function test_the_page_shows_each_scanner_with_its_state_and_history(): void
    {
        $active = $this->makeScanner('IN', true, $this->ago(60), $this->ago(2));
        $inactive = $this->makeScanner('OUT', false, $this->ago(30), $this->ago(2));
        DB::table('rfid_scanner_status_logs')->insert(['yard_id' => $this->yard->id, 'scanner_name' => 'OUT', 'is_active' => 0, 'reported_at' => '2026-11-03 09:30:00']);
        DB::table('rfid_scanner_status_logs')->insert(['yard_id' => $this->yard->id, 'scanner_name' => 'IN', 'is_active' => 1, 'reported_at' => '2026-11-03 09:00:00']);
        $this->signInAs('super_admin');

        Livewire::test(ListRfidScannerStatuses::class)
            ->assertCanSeeTableRecords([$active, $inactive])
            ->assertTableColumnStateSet('state', RfidScannerStatus::ACTIVE, $active)
            ->assertTableColumnStateSet('state', RfidScannerStatus::INACTIVE, $inactive);

        Livewire::test(ViewRfidScannerStatus::class, ['record' => $inactive->getKey()])
            ->assertOk()
            ->assertSee('Inactive');

        Livewire::test(LogsRelationManager::class, ['ownerRecord' => $inactive, 'pageClass' => ViewRfidScannerStatus::class])
            ->assertCountTableRecords(1);
    }

    public function test_the_page_is_read_only_and_needs_the_permission(): void
    {
        $scanner = $this->makeScanner('IN', true, $this->ago(60), $this->ago(2));
        $this->signInAs('viewer');

        $this->assertFalse(RfidScannerStatusResource::canViewAny());
        Livewire::test(ListRfidScannerStatuses::class)->assertForbidden();

        $this->signInAs('viewer', ['ViewAny:RfidScannerStatusResource', 'View:RfidScannerStatusResource', 'Update:RfidScannerStatusResource', 'Delete:RfidScannerStatusResource']);

        $this->assertTrue(RfidScannerStatusResource::canViewAny());
        $this->assertFalse(RfidScannerStatusResource::canCreate());
        $this->assertFalse(RfidScannerStatusResource::canEdit($scanner));
        $this->assertFalse(RfidScannerStatusResource::canDelete($scanner));
        $this->assertArrayNotHasKey('create', RfidScannerStatusResource::getPages());
        $this->assertArrayNotHasKey('edit', RfidScannerStatusResource::getPages());
    }

    public function test_the_dashboard_widget_shows_a_tile_per_scanner_once_there_is_a_report(): void
    {
        $this->signInAs('super_admin');
        $this->assertFalse(RfidScannerStatusStats::canView());

        $this->makeScanner('IN', true, $this->ago(60), $this->ago(2));
        $this->makeScanner('OUT', false, $this->ago(30), $this->ago(2));

        $this->assertTrue(RfidScannerStatusStats::canView());
        Livewire::test(RfidScannerStatusStats::class)
            ->assertSee('Valenzuela Yard · IN')
            ->assertSee('Valenzuela Yard · OUT')
            ->assertSee('Active')
            ->assertSee('Inactive');
    }

    public function test_the_migration_adds_the_permissions_and_gives_them_to_the_asset_admin_role(): void
    {
        $role = Role::create(['name' => 'asset_admin', 'guard_name' => 'web']);

        (require base_path('database/migrations/2026_11_03_144744_add_rfid_scanner_status_permissions.php'))->up();

        $this->assertTrue(Permission::where('name', 'ViewAny:RfidScannerStatusResource')->exists());
        $this->assertTrue($role->fresh()->hasPermissionTo('View:RfidScannerStatusResource'));
    }
}

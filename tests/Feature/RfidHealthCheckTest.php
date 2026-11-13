<?php

namespace Tests\Feature;

use App\Mail\RfidHealthAlertMail;
use App\Models\AppUser;
use App\Models\AssetTag;
use App\Models\RfidHealthState;
use App\Services\Rfid\RfidHealthCheck;
use App\Services\Rfid\RfidHealthReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class RfidHealthCheckTest extends AssetMovementTestCase
{
    private AppUser $service;

    private AppUser $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        (require base_path('database/migrations/2026_10_09_090035_create_notifications_table.php'))->up();
        (require base_path('database/migrations/2026_11_03_112117_create_rfid_health_states_table.php'))->up();

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->superAdmin = $this->makeUser('Sam', 'Admin');
        $this->superAdmin->assignRole('super_admin');

        $this->service = AppUser::create(['user_fname' => 'RFID', 'user_lname' => 'Integration', 'user_email' => 'rfid-integration@emms.invalid', 'user_dep_id' => 6, 'is_active' => false]);

        // A Tuesday morning, inside the default alert window.
        Carbon::setTestNow('2026-11-03 10:00:00');

        config([
            'rfid.alerts.enabled' => true,
            'rfid.alerts.emails' => 'it.admin@ravago.com.ph',
            'rfid.alerts.silence_minutes' => 60,
            'rfid.alerts.no_scans_minutes' => 240,
            'rfid.alerts.window_start' => '06:00',
            'rfid.alerts.window_end' => '20:00',
            'rfid.alerts.days' => '1,2,3,4,5,6',
            'rfid.alerts.reminder_hours' => 6,
            'rfid.alerts.token_warning_days' => 30,
        ]);

        Mail::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function issueToken(?int $expiresInDays = null, ?string $lastUsedAt = null): void
    {
        $token = $this->service->createToken(config('rfid.token_name'), ['rfid:write'], $expiresInDays === null ? null : now()->addDays($expiresInDays));
        $token->accessToken->forceFill(['last_used_at' => $lastUsedAt])->save();
    }

    /**
     * A stored scan, received this many minutes ago by the database's own clock (which is what the check uses).
     */
    private function storeScan(int $minutesAgo): void
    {
        static $count = 0;
        $tag = AssetTag::find('SCAN1') ?? $this->makeTag($this->makeEquipment(), 'SCAN1');
        $log = $this->scan($tag, 'IN', now()->subMinutes(++$count)->toDateTimeString());

        DB::table('asset_tag_logs')->where('id', $log->id)->update(['received_at' => DB::raw("datetime('now', '-{$minutesAgo} minutes')")]);
    }

    private function check(): void
    {
        $this->artisan('rfid:check-health')->assertSuccessful();
    }

    public function test_a_reader_that_is_sending_and_storing_scans_is_healthy(): void
    {
        $this->issueToken(lastUsedAt: now()->subMinutes(5)->toDateTimeString());
        $this->storeScan(10);

        $this->assertSame(RfidHealthReport::OK, app(RfidHealthCheck::class)->inspect()->status);

        $this->check();

        Mail::assertNothingQueued();
        $this->assertSame(0, DB::table('notifications')->count());
    }

    public function test_no_valid_token_is_reported_as_down_whether_it_expired_or_never_existed(): void
    {
        $report = app(RfidHealthCheck::class)->inspect();
        $this->assertSame([RfidHealthReport::DOWN, RfidHealthReport::CAUSE_NO_TOKEN], [$report->status, $report->cause]);

        $token = $this->service->createToken(config('rfid.token_name'), ['rfid:write'], now()->subDay());
        $this->assertSame(RfidHealthReport::CAUSE_NO_TOKEN, app(RfidHealthCheck::class)->inspect()->cause);
        $this->assertNotNull($token);
    }

    public function test_a_reader_server_that_has_not_contacted_emms_is_down(): void
    {
        $this->issueToken(lastUsedAt: now()->subMinutes(61)->toDateTimeString());
        $this->storeScan(300);

        $report = app(RfidHealthCheck::class)->inspect();

        $this->assertSame([RfidHealthReport::DOWN, RfidHealthReport::CAUSE_NO_CONTACT], [$report->status, $report->cause]);
    }

    public function test_a_token_that_was_never_used_is_down(): void
    {
        $this->issueToken();

        $this->assertSame(RfidHealthReport::CAUSE_NO_CONTACT, app(RfidHealthCheck::class)->inspect()->cause);
    }

    public function test_a_reader_that_contacts_emms_but_stores_no_scans_is_only_quiet(): void
    {
        $this->issueToken(lastUsedAt: now()->subMinutes(2)->toDateTimeString());
        $this->storeScan(300);

        $report = app(RfidHealthCheck::class)->inspect();

        $this->assertSame([RfidHealthReport::QUIET, RfidHealthReport::CAUSE_NO_SCANS], [$report->status, $report->cause]);

        // The same reader is fine again once a scan was stored recently, even if the server is quiet for a while.
        $this->storeScan(30);
        $this->assertTrue(app(RfidHealthCheck::class)->inspect()->isHealthy());
    }

    public function test_nothing_is_sent_or_remembered_while_alerts_are_off(): void
    {
        config(['rfid.alerts.enabled' => false]);

        $this->check();

        Mail::assertNothingQueued();
        $this->assertSame(0, RfidHealthState::count());
    }

    public function test_a_problem_is_announced_once_by_email_and_in_the_app_then_reminded_then_recovered(): void
    {
        $this->check(); // no token

        Mail::assertQueued(RfidHealthAlertMail::class, 1);
        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->hasTo('it.admin@ravago.com.ph')
            && $mail->kind === RfidHealthAlertMail::PROBLEM
            && str_contains($mail->headline, 'no valid token'));
        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', $this->superAdmin->user_id)->count());
        $this->assertSame(0, DB::table('notifications')->where('notifiable_id', $this->service->user_id)->count(), 'the inactive service account is not notified');

        // The next checks stay quiet.
        Carbon::setTestNow('2026-11-03 10:10:00');
        $this->check();
        Carbon::setTestNow('2026-11-03 15:50:00');
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 1);

        // Six hours after the first alert: a reminder.
        Carbon::setTestNow('2026-11-03 16:00:00');
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 2);
        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->kind === RfidHealthAlertMail::REMINDER);

        // Fixed: one recovery message, then silence.
        $this->issueToken(lastUsedAt: now()->toDateTimeString());
        $this->storeScan(1);
        Carbon::setTestNow('2026-11-03 16:10:00');
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->kind === RfidHealthAlertMail::RECOVERED);
        Mail::assertQueued(RfidHealthAlertMail::class, 3);
        $this->assertSame(RfidHealthReport::OK, RfidHealthState::current()->status);

        Carbon::setTestNow('2026-11-03 16:20:00');
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 3);
    }

    public function test_the_alert_state_survives_a_cache_clear_so_nobody_is_alerted_twice(): void
    {
        $this->check();
        Cache::flush();
        Carbon::setTestNow('2026-11-03 10:10:00');
        $this->check();

        Mail::assertQueued(RfidHealthAlertMail::class, 1);
    }

    public function test_alerts_wait_for_the_alert_window_and_nobody_is_told_about_a_problem_they_never_heard_of(): void
    {
        Carbon::setTestNow('2026-11-03 22:00:00'); // after 20:00
        $this->check();
        Mail::assertNothingQueued();

        Carbon::setTestNow('2026-11-08 10:00:00'); // a Sunday
        $this->check();
        Mail::assertNothingQueued();

        // It clears by itself overnight: no "recovered" without an earlier alert.
        $this->issueToken(lastUsedAt: now()->toDateTimeString());
        $this->storeScan(1);
        Carbon::setTestNow('2026-11-09 07:00:00');
        $this->check();
        Mail::assertNothingQueued();

        // A problem that is still there when the window opens is announced then.
        DB::table('personal_access_tokens')->delete();
        Carbon::setTestNow('2026-11-09 07:10:00');
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 1);
    }

    public function test_a_different_problem_is_announced_right_away_even_inside_the_reminder_period(): void
    {
        $this->check(); // no token
        Mail::assertQueued(RfidHealthAlertMail::class, 1);

        $this->issueToken(); // token back, but the reader server never used it
        Carbon::setTestNow('2026-11-03 10:10:00');
        $this->check();

        Mail::assertQueued(RfidHealthAlertMail::class, 2);
        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => str_contains($mail->headline, 'not reaching EMS'));
    }

    public function test_recipients_are_cleaned_up_before_sending(): void
    {
        config(['rfid.alerts.emails' => ' it.admin@ravago.com.ph, ,not-an-email,second@ravago.com.ph,it.admin@ravago.com.ph']);

        $this->check();

        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->hasTo('it.admin@ravago.com.ph')
            && $mail->hasTo('second@ravago.com.ph')
            && count($mail->to) === 2);
    }

    public function test_a_failing_mailer_does_not_stop_the_check(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->check();

        $this->assertSame(1, DB::table('notifications')->count(), 'the in-app alert is still created');
        $this->assertNotNull(RfidHealthState::current()->last_notified_at);
    }

    public function test_a_token_about_to_expire_is_warned_about_once_a_week(): void
    {
        $this->issueToken(10, now()->toDateTimeString());
        $this->storeScan(1);

        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 1);
        Mail::assertQueued(RfidHealthAlertMail::class, fn (RfidHealthAlertMail $mail): bool => $mail->kind === RfidHealthAlertMail::TOKEN_EXPIRING);

        Carbon::setTestNow('2026-11-05 10:00:00');
        DB::table('personal_access_tokens')->update(['last_used_at' => now()]);
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 1);

        Carbon::setTestNow('2026-11-10 10:00:00');
        DB::table('personal_access_tokens')->update(['last_used_at' => now()]);
        $this->storeScan(1);
        $this->check();
        Mail::assertQueued(RfidHealthAlertMail::class, 2);
    }

    public function test_a_token_that_never_expires_or_expires_far_away_is_not_warned_about(): void
    {
        $this->issueToken(lastUsedAt: now()->toDateTimeString());
        $this->issueToken(10, now()->toDateTimeString());
        $this->storeScan(1);

        $this->check();
        Mail::assertNothingQueued();

        DB::table('personal_access_tokens')->whereNull('expires_at')->delete();
        DB::table('personal_access_tokens')->update(['expires_at' => now()->addDays(200)]);
        $this->check();
        Mail::assertNothingQueued();
    }

    public function test_new_tokens_do_not_expire_unless_days_are_given(): void
    {
        $this->artisan('rfid:issue-token')->expectsOutputToContain('does not expire')->assertSuccessful();
        $this->assertNull(DB::table('personal_access_tokens')->first()->expires_at);

        $this->artisan('rfid:issue-token', ['--days' => 30, '--revoke' => true])->assertSuccessful();
        $tokens = DB::table('personal_access_tokens')->get();
        $this->assertCount(1, $tokens);
        $this->assertSame(now()->addDays(30)->toDateString(), Carbon::parse($tokens->first()->expires_at)->toDateString());
    }

    public function test_a_bad_days_value_is_refused(): void
    {
        $this->artisan('rfid:issue-token', ['--days' => 'soon'])->assertFailed();
        $this->artisan('rfid:issue-token', ['--days' => 0])->assertFailed();
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }
}

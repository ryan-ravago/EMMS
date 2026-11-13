<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Services\Google\GmailWorkOrderReplySyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery\MockInterface;
use Tests\TestCase;

class SyncGoogleWorkOrderRepliesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'activitylog.enabled' => false,
            'work_orders.reply_sync_enabled' => true,
            'work_orders.gmail_account_email' => null,
        ]);

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('google_access_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->dateTime('google_token_expires_at')->nullable();
        });
    }

    private function makeUser(string $email, bool $signedInWithGoogle = true): AppUser
    {
        return AppUser::create([
            'user_fname' => 'Mail',
            'user_lname' => 'Box',
            'user_email' => $email,
            'is_active' => true,
            'google_refresh_token' => $signedInWithGoogle ? 'refresh-token' : null,
        ]);
    }

    private function expectNoSync(): void
    {
        $this->mock(GmailWorkOrderReplySyncService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('syncMailbox'));
    }

    private function expectSyncOf(AppUser $user): void
    {
        $this->mock(GmailWorkOrderReplySyncService::class, fn (MockInterface $mock) => $mock
            ->shouldReceive('syncMailbox')
            ->once()
            ->withArgs(fn (AppUser $mailboxUser): bool => $mailboxUser->is($user))
            ->andReturn(0));
    }

    public function test_the_configured_mailbox_is_synced_whatever_the_letter_case(): void
    {
        $this->makeUser('someone.else@example.test');
        $mailbox = $this->makeUser('WorkOrders@example.test');
        config(['work_orders.gmail_account_email' => ' workorders@EXAMPLE.test ']);

        $this->expectSyncOf($mailbox);

        $this->artisan('work-orders:sync-google-replies')
            ->expectsOutputToContain('Processed 0 Gmail reply message(s).')
            ->assertSuccessful();
    }

    public function test_the_email_option_overrides_the_configured_mailbox(): void
    {
        $this->makeUser('workorders@example.test');
        $other = $this->makeUser('other.mailbox@example.test');
        config(['work_orders.gmail_account_email' => 'workorders@example.test']);

        $this->expectSyncOf($other);

        $this->artisan('work-orders:sync-google-replies', ['--email' => 'other.mailbox@example.test'])->assertSuccessful();
    }

    public function test_nothing_is_synced_when_no_mailbox_is_set_even_if_users_have_signed_in_with_google(): void
    {
        $this->makeUser('someone@example.test');

        $this->expectNoSync();

        $this->artisan('work-orders:sync-google-replies')
            ->expectsOutputToContain('WORK_ORDER_GMAIL_ACCOUNT')
            ->assertSuccessful();
    }

    public function test_nothing_is_synced_when_the_sync_is_switched_off(): void
    {
        $this->makeUser('workorders@example.test');
        config(['work_orders.gmail_account_email' => 'workorders@example.test', 'work_orders.reply_sync_enabled' => false]);

        $this->expectNoSync();

        $this->artisan('work-orders:sync-google-replies')
            ->expectsOutputToContain('switched off')
            ->assertSuccessful();
    }

    public function test_nothing_is_synced_when_the_mailbox_is_not_an_emms_user(): void
    {
        $this->makeUser('someone@example.test');
        config(['work_orders.gmail_account_email' => 'workorders@example.test']);

        $this->expectNoSync();

        $this->artisan('work-orders:sync-google-replies')
            ->expectsOutputToContain('No EMS user has the email workorders@example.test')
            ->assertSuccessful();
    }

    public function test_nothing_is_synced_when_the_mailbox_user_has_not_signed_in_with_google(): void
    {
        $this->makeUser('workorders@example.test', signedInWithGoogle: false);
        config(['work_orders.gmail_account_email' => 'workorders@example.test']);

        $this->expectNoSync();

        $this->artisan('work-orders:sync-google-replies')
            ->expectsOutputToContain('has not signed in to EMS with Google')
            ->assertSuccessful();
    }

    public function test_a_failing_sync_fails_the_command(): void
    {
        $this->makeUser('workorders@example.test');
        config(['work_orders.gmail_account_email' => 'workorders@example.test']);

        $this->mock(GmailWorkOrderReplySyncService::class, fn (MockInterface $mock) => $mock
            ->shouldReceive('syncMailbox')
            ->andThrow(new \RuntimeException('Unable to refresh the Google access token.')));

        $this->artisan('work-orders:sync-google-replies')
            ->expectsOutputToContain('Unable to refresh the Google access token.')
            ->assertFailed();
    }

    public function test_the_gmail_search_follows_the_settings(): void
    {
        $service = app(GmailWorkOrderReplySyncService::class);

        config(['work_orders.reply_max_age_days' => 7, 'work_orders.reply_batch_size' => 50]);
        $this->assertSame(['q' => 'is:unread newer_than:7d', 'maxResults' => 50], $service->listOptions());

        config(['work_orders.reply_max_age_days' => 3, 'work_orders.reply_batch_size' => 20]);
        $this->assertSame(['q' => 'is:unread newer_than:3d', 'maxResults' => 20], $service->listOptions());

        config(['work_orders.reply_max_age_days' => 0, 'work_orders.reply_batch_size' => 0]);
        $this->assertSame(['q' => 'is:unread newer_than:1d', 'maxResults' => 1], $service->listOptions());
    }
}

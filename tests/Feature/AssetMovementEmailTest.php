<?php

namespace Tests\Feature;

use App\Filament\Pages\MyProfile;
use App\Mail\AssetMovementMail;
use App\Models\AppUser;
use App\Models\Equipment;
use App\Services\Rfid\MovementNotifier;
use App\Services\Rfid\MovementRecorder;
use App\Services\Rfid\RawLogProcessor;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssetMovementEmailTest extends AssetMovementTestCase
{
    private AppUser $admin;

    private AppUser $manager;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-04 12:00:00');
        Mail::fake();
        config([
            'rfid.movement_emails.enabled' => true,
            'rfid.movement_emails.max_age_minutes' => 180,
            'rfid.movement_window_seconds' => 300,
            'rfid.raw_log_retention_days' => 7,
        ]);

        foreach (['super_admin', 'asset_admin', 'asset_manager', 'viewer'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // The My Profile page shows the person's department.
        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code')->nullable();
            $table->string('dep_name')->nullable();
            $table->boolean('is_maintenance')->default(false);
        });
        DB::table('departments')->insert(['dep_id' => 6, 'dep_code' => 'MNT', 'dep_name' => 'Maintenance']);

        // Everyone may open their own My Profile page (Shield page permission).
        $viewProfile = Permission::create(['name' => 'View:MyProfile', 'guard_name' => 'web']);
        Role::findByName('asset_admin')->givePermissionTo($viewProfile);
        Role::findByName('asset_manager')->givePermissionTo($viewProfile);
        Role::findByName('viewer')->givePermissionTo($viewProfile);
        Role::findByName('super_admin')->givePermissionTo($viewProfile);

        // Asset admin and asset manager start out with the "Receive Emails" permission (the migration grants it).
        (require base_path('database/migrations/2026_11_04_152458_add_receive_emails_permission_to_asset_movements.php'))->up();

        $this->admin = $this->userWithRole('asset_admin', 'Alice', 'Admin');
        $this->manager = $this->userWithRole('asset_manager', 'Mark', 'Manager');
    }

    private function userWithRole(string $role, string $first, string $last): AppUser
    {
        // Reloaded so the column defaults (switched on) are on the model, as they are for a user loaded from the database.
        $user = $this->makeUser($first, $last)->refresh();
        $user->assignRole($role);

        return $user;
    }

    private function recorder(): MovementRecorder
    {
        return app(MovementRecorder::class);
    }

    /**
     * @return list<string>
     */
    private function recipientsOf(AssetMovementMail $mail): array
    {
        return collect($mail->to)->pluck('address')->sort()->values()->all();
    }

    private function emailsOf(AppUser ...$users): array
    {
        return collect($users)->pluck('user_email')->sort()->values()->all();
    }

    public function test_a_time_out_emails_the_asset_admins_and_managers_once(): void
    {
        $tag = $this->makeTag($equipment = $this->makeEquipment(), 'AAA1');

        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);

        Mail::assertQueuedCount(1);
        Mail::assertQueued(AssetMovementMail::class, function (AssetMovementMail $mail) use ($equipment): bool {
            return $mail->kind === 'out'
                && $this->recipientsOf($mail) === $this->emailsOf($this->admin, $this->manager)
                && str_contains($mail->assetLabel, $equipment->eqm_name)
                && $mail->details['Time out'] === 'Nov 04, 2026 11:30 AM'
                && $mail->details['Yard'] === 'Valenzuela Yard'
                && $mail->envelope()->subject === "[EMMS] Time out: {$mail->assetLabel}";
        });
    }

    public function test_an_asset_with_several_tags_sends_one_email_not_one_per_tag(): void
    {
        $equipment = $this->makeEquipment();
        $first = $this->makeTag($equipment, 'AAA1');
        $second = $this->makeTag($equipment, 'AAA2');
        $third = $this->makeTag($equipment, 'AAA3');

        $this->recorder()->record([
            $this->scan($first, 'OUT', '2026-11-04 11:30:00'),
            $this->scan($second, 'OUT', '2026-11-04 11:30:05'),
            $this->scan($third, 'OUT', '2026-11-04 11:30:09'),
        ]);

        Mail::assertQueuedCount(1);

        // And the way back: three tags read again, still one email.
        $this->recorder()->record([
            $this->scan($first, 'IN', '2026-11-04 11:50:00'),
            $this->scan($second, 'IN', '2026-11-04 11:50:04'),
            $this->scan($third, 'IN', '2026-11-04 11:50:08'),
        ]);

        Mail::assertQueuedCount(2);
        Mail::assertQueued(AssetMovementMail::class, fn (AssetMovementMail $mail): bool => $mail->kind === 'in');
    }

    public function test_a_time_in_says_when_the_asset_went_out_and_how_long_it_was_away(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 09:00:00')]);

        $this->recorder()->record([$this->scan($tag, 'IN', '2026-11-04 11:30:00')]);

        Mail::assertQueued(AssetMovementMail::class, function (AssetMovementMail $mail): bool {
            return $mail->kind === 'in'
                && $mail->details['Time in'] === 'Nov 04, 2026 11:30 AM'
                && $mail->details['Out since'] === 'Nov 04, 2026 09:00 AM'
                && isset($mail->details['Time away'])
                && $mail->note === null;
        });
    }

    public function test_the_email_reads_clearly(): void
    {
        $equipment = $this->makeEquipment();
        $tag = $this->makeTag($equipment, 'AAA1');
        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);

        $html = null;
        Mail::assertQueued(AssetMovementMail::class, function (AssetMovementMail $mail) use (&$html): bool {
            $html = $mail->render();

            return true;
        });

        $this->assertStringContainsString('Time out: ', $html);
        $this->assertStringContainsString($equipment->eqm_name, $html);
        $this->assertStringContainsString('Nov 04, 2026 11:30 AM', $html);
        $this->assertStringContainsString('Valenzuela Yard', $html);
        $this->assertStringContainsString('My Profile', $html);
        // About the asset, never the RFID tag.
        $this->assertStringNotContainsString('AAA1', $html);
    }

    public function test_a_time_in_without_a_time_out_is_flagged_in_the_email(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([$this->scan($tag, 'IN', '2026-11-04 11:30:00')]);

        Mail::assertQueued(AssetMovementMail::class, fn (AssetMovementMail $mail): bool => $mail->kind === 'in'
            && ! isset($mail->details['Out since'])
            && str_contains((string) $mail->note, 'No time out was recorded'));
    }

    public function test_service_vehicles_are_tracked_and_emailed_too(): void
    {
        $vehicle = Equipment::factory()->serviceVehicle()->create();
        $tag = $this->makeTag($vehicle, 'VEH1');

        $result = $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);

        $this->assertSame(1, $result['opened']);
        Mail::assertQueued(AssetMovementMail::class, fn (AssetMovementMail $mail): bool => $mail->assetType === 'Service Vehicle');
    }

    public function test_accessories_send_nothing(): void
    {
        $tag = $this->makeTag($this->makeAccessory(), 'ACC1');

        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);

        Mail::assertNothingQueued();
    }

    public function test_the_same_scans_recorded_again_send_nothing_more(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $logs = [$this->scan($tag, 'OUT', '2026-11-04 11:30:00')];

        $this->recorder()->record($logs);
        $this->recorder()->record($logs);

        Mail::assertQueuedCount(1);
    }

    public function test_super_admins_asset_admins_and_managers_are_emailed_but_not_inactive_or_switched_off_users(): void
    {
        $super = $this->userWithRole('super_admin', 'Super', 'Admin');
        $off = $this->userWithRole('asset_manager', 'Off', 'Manager');
        $off->update(['notify_asset_movements' => false]);
        $inactive = $this->userWithRole('asset_admin', 'Gone', 'Admin');
        $inactive->update(['is_active' => false]);
        $this->userWithRole('viewer', 'View', 'Er');
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);

        Mail::assertQueuedCount(1);
        Mail::assertQueued(AssetMovementMail::class, fn (AssetMovementMail $mail): bool => $this->recipientsOf($mail) === $this->emailsOf($this->admin, $this->manager, $super));
    }

    public function test_the_roles_that_are_emailed_follow_the_shield_permission(): void
    {
        $dispatcher = Role::create(['name' => 'dispatcher', 'guard_name' => 'web']);
        $dispatch = $this->userWithRole('dispatcher', 'Dee', 'Dispatch');
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        // A role without the permission is not emailed.
        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);
        Mail::assertQueued(AssetMovementMail::class, fn (AssetMovementMail $mail): bool => ! in_array($dispatch->user_email, $this->recipientsOf($mail), true));

        // Ticking "Receive Emails" for the role in Shield adds it...
        $dispatcher->givePermissionTo(MovementNotifier::PERMISSION);
        $this->assertContains($dispatch->user_email, app(MovementNotifier::class)->recipients());

        // ...and taking it away from asset manager removes them.
        Role::findByName('asset_manager')->revokePermissionTo(MovementNotifier::PERMISSION);
        $recipients = app(MovementNotifier::class)->recipients();
        $this->assertNotContains($this->manager->user_email, $recipients);
        $this->assertContains($this->admin->user_email, $recipients);
    }

    public function test_a_single_user_can_be_given_the_permission_directly(): void
    {
        $viewer = $this->userWithRole('viewer', 'View', 'Er');
        $viewer->givePermissionTo(MovementNotifier::PERMISSION);

        $this->assertContains($viewer->user_email, app(MovementNotifier::class)->recipients());
    }

    public function test_only_super_admins_are_emailed_until_the_permission_exists(): void
    {
        Permission::where('name', MovementNotifier::PERMISSION)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $super = $this->userWithRole('super_admin', 'Super', 'Admin');

        $this->assertSame([$super->user_email], app(MovementNotifier::class)->recipients());
    }

    public function test_nothing_is_sent_when_everyone_has_switched_it_off(): void
    {
        $this->admin->update(['notify_asset_movements' => false]);
        $this->manager->update(['notify_asset_movements' => false]);
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);

        Mail::assertNothingQueued();
    }

    public function test_the_master_switch_turns_every_email_off(): void
    {
        config(['rfid.movement_emails.enabled' => false]);
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);

        Mail::assertNothingQueued();
    }

    public function test_old_scans_are_history_and_send_nothing(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([
            $this->scan($tag, 'OUT', '2026-11-03 08:00:00'),
            $this->scan($tag, 'IN', '2026-11-03 17:00:00'),
        ]);

        Mail::assertNothingQueued();
        $this->assertSame(1, DB::table('asset_movements')->count());
    }

    public function test_a_failing_email_does_not_undo_the_trip(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');

        $this->recorder()->record([$this->scan($tag, 'OUT', '2026-11-04 11:30:00')]);

        $this->assertSame(1, DB::table('asset_movements')->count());
    }

    public function test_a_rolled_back_run_sends_nothing(): void
    {
        $tag = $this->makeTag($this->makeEquipment(), 'AAA1');
        $log = $this->scan($tag, 'OUT', '2026-11-04 11:30:00');

        DB::beginTransaction();
        $this->recorder()->record([$log]);
        DB::rollBack();

        Mail::assertNothingQueued();
    }

    public function test_a_scan_that_arrives_through_the_reader_api_pipeline_sends_one_email(): void
    {
        $equipment = $this->makeEquipment();
        $this->makeTag($equipment, 'AAA1');
        $this->makeTag($equipment, 'AAA2');
        foreach (['AAA1' => '11:30:00', 'AAA2' => '11:30:06'] as $tag => $time) {
            DB::table('rfid_raw_logs')->insert([
                'batch_uuid' => 'b1', 'yard_id' => $this->yard->id, 'tag_id' => $tag,
                'status' => 'OUT', 'detected_at' => "2026-11-04 {$time}",
            ]);
        }

        app(RawLogProcessor::class)->run();

        Mail::assertQueuedCount(1);
        $this->assertSame(1, DB::table('asset_movements')->count());
    }

    // ---- My Profile switch -------------------------------------------------------------------------------

    public function test_an_asset_admin_can_switch_the_emails_off_and_on_from_my_profile(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs($this->admin);

        Livewire::test(MyProfile::class)
            ->assertSchemaStateSet(['notify_asset_movements' => true])
            ->assertSee('Email notifications')
            ->fillForm(['notify_asset_movements' => false])
            ->assertNotified();

        $this->assertFalse($this->admin->fresh()->notify_asset_movements);

        Livewire::test(MyProfile::class)
            ->assertSchemaStateSet(['notify_asset_movements' => false])
            ->fillForm(['notify_asset_movements' => true]);

        $this->assertTrue($this->admin->fresh()->notify_asset_movements);
    }

    public function test_super_admins_and_roles_with_the_permission_see_the_switch(): void
    {
        Filament::setCurrentPanel('admin');
        $notifier = app(MovementNotifier::class);

        $super = $this->userWithRole('super_admin', 'Super', 'Admin');
        $this->assertTrue($notifier->canReceive($super));
        $this->actingAs($super);
        Livewire::test(MyProfile::class)->assertSee('Email notifications');

        $dispatcher = Role::create(['name' => 'dispatcher', 'guard_name' => 'web']);
        $dispatcher->givePermissionTo(['View:MyProfile', MovementNotifier::PERMISSION]);
        $dispatch = $this->userWithRole('dispatcher', 'Dee', 'Dispatch');
        $this->assertTrue($notifier->canReceive($dispatch));
        $this->actingAs($dispatch);
        Livewire::test(MyProfile::class)
            ->assertSee('Email notifications')
            ->fillForm(['notify_asset_movements' => false]);

        $this->assertFalse($dispatch->fresh()->notify_asset_movements);
    }

    public function test_other_roles_do_not_see_the_switch_and_cannot_change_it(): void
    {
        Filament::setCurrentPanel('admin');
        $viewer = $this->userWithRole('viewer', 'View', 'Er');
        $this->actingAs($viewer);

        Livewire::test(MyProfile::class)
            ->assertDontSee('Email notifications')
            ->fillForm(['notify_asset_movements' => false]);

        $this->assertTrue($viewer->fresh()->notify_asset_movements);
    }
}

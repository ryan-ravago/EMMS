<?php

namespace Tests\Feature;

use App\Filament\Resources\AssetMovements\Pages\ListAssetMovements;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Equipment\RelationManagers\MovementsRelationManager;
use App\Mail\AssetMovementMail;
use App\Models\AppUser;
use App\Models\AssetMovement;
use App\Models\Equipment;
use App\Models\Location;
use App\Services\Rfid\MovementRecorder;
use Carbon\Carbon;
use DomainException;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssetMovementCreateTripTest extends AssetMovementTestCase
{
    private AppUser $manager;

    private AppUser $admin;

    private AppUser $viewer;

    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-05 12:00:00');
        Mail::fake();
        config([
            'rfid.movement_emails.enabled' => true,
            'rfid.movement_emails.max_age_minutes' => 180,
            'rfid.movement_window_seconds' => 300,
        ]);

        foreach (['super_admin', 'asset_admin', 'asset_manager', 'viewer'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // The migrations that grant the permissions are part of what is tested.
        (require base_path('database/migrations/2026_10_29_121723_add_asset_movement_permissions.php'))->up();
        (require base_path('database/migrations/2026_11_04_152458_add_receive_emails_permission_to_asset_movements.php'))->up();
        (require base_path('database/migrations/2026_11_05_090000_add_create_trip_permission_to_asset_movements.php'))->up();
        (require base_path('database/migrations/2026_11_04_165550_ensure_manual_in_for_super_admin_and_asset_admin.php'))->up();
        Role::findByName('viewer')->givePermissionTo(Permission::findOrCreate('ViewAny:AssetMovementResource', 'web'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Filament::setCurrentPanel('admin');

        $this->manager = $this->makeUser('Mario', 'Manager')->refresh();
        $this->manager->assignRole('asset_manager');
        $this->admin = $this->makeUser('Alma', 'Admin')->refresh();
        $this->admin->assignRole('asset_admin');
        $this->viewer = $this->makeUser('View', 'Er')->refresh();
        $this->viewer->assignRole('viewer');

        $this->equipment = $this->makeEquipment();
    }

    private function create(?Equipment $asset = null, string $out = '2026-11-05 08:00:00', ?string $in = null, ?int $yardIn = null, string $remarks = 'No scanner at this yard'): AssetMovement
    {
        return AssetMovement::recordManualTrip(
            $asset ?? $this->equipment,
            Carbon::parse($out),
            $this->yard->id,
            $in === null ? null : Carbon::parse($in),
            $in === null ? null : ($yardIn ?? $this->yard->id),
            $this->manager->user_id,
            $remarks,
        );
    }

    // ---- the rule ----------------------------------------------------------------------------------------

    public function test_a_trip_with_only_a_time_out_is_open_and_entered_manually(): void
    {
        $trip = $this->create();

        $this->assertSame(AssetMovement::STATUS_MISSING_TIME_IN, $trip->status);
        $this->assertTrue($trip->isManualOut());
        $this->assertFalse($trip->isManualIn());
        $this->assertSame($this->manager->user_id, $trip->manual_out_by);
        $this->assertSame('No scanner at this yard', $trip->manual_out_remarks);
        $this->assertSame($this->yard->id, $trip->yard_out_id);
        $this->assertNull($trip->tag_id);
        $this->assertNull($trip->out_log_id);
        $this->assertNull($trip->in_log_id);
        $this->assertNull($trip->acknowledged_at);
    }

    public function test_a_whole_trip_is_acknowledged_straight_away(): void
    {
        $otherYard = Location::create(['name' => 'Cebu Yard']);

        $trip = $this->create(in: '2026-11-05 10:30:00', yardIn: $otherYard->id);

        $this->assertSame(AssetMovement::STATUS_ACKNOWLEDGED, $trip->status);
        $this->assertTrue($trip->isManualOut());
        $this->assertTrue($trip->isManualIn());
        $this->assertSame($otherYard->id, $trip->yard_in_id);
        $this->assertSame($this->manager->user_id, $trip->acknowledged_by);
    }

    public function test_service_vehicles_can_have_trips_but_accessories_cannot(): void
    {
        $this->assertSame(1, $this->create(Equipment::factory()->serviceVehicle()->create())->asset_id > 0 ? 1 : 0);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Only equipment and service vehicles');
        $this->create($this->makeAccessory());
    }

    public function test_the_times_must_make_sense(): void
    {
        $cases = [
            'time out in the future' => fn () => $this->create(out: '2026-11-05 13:00:00'),
            'time in in the future' => fn () => $this->create(out: '2026-11-05 08:00:00', in: '2026-11-05 13:00:00'),
            'time in before time out' => fn () => $this->create(out: '2026-11-05 09:00:00', in: '2026-11-05 08:00:00'),
        ];

        foreach ($cases as $name => $attempt) {
            try {
                $attempt();
                $this->fail("Expected a DomainException for: {$name}");
            } catch (DomainException) {
                // expected
            }
        }

        $this->assertSame(0, AssetMovement::count());
    }

    public function test_a_time_in_needs_a_yard(): void
    {
        $this->expectException(DomainException::class);

        AssetMovement::recordManualTrip($this->equipment, Carbon::parse('2026-11-05 08:00'), $this->yard->id, Carbon::parse('2026-11-05 09:00'), null, $this->manager->user_id, 'x');
    }

    public function test_equipment_that_is_still_out_cannot_go_out_again(): void
    {
        $open = $this->create(out: '2026-11-05 07:00:00');

        try {
            $this->create(out: '2026-11-05 09:00:00');
            $this->fail('Expected a DomainException');
        } catch (DomainException $exception) {
            $this->assertStringContainsString("trip #{$open->getKey()}", $exception->getMessage());
            $this->assertStringContainsString('still out', $exception->getMessage());
        }

        $this->assertSame(1, AssetMovement::count());
    }

    public function test_a_trip_cannot_overlap_another_trip_of_the_same_equipment(): void
    {
        $this->create(out: '2026-11-05 08:00:00', in: '2026-11-05 10:00:00');

        foreach ([['2026-11-05 09:00:00', null], ['2026-11-05 07:00:00', '2026-11-05 08:30:00'], ['2026-11-05 07:00:00', '2026-11-05 11:00:00']] as [$out, $in]) {
            try {
                $this->create(out: $out, in: $in);
                $this->fail("Expected an overlap for {$out} - {$in}");
            } catch (DomainException $exception) {
                $this->assertStringContainsString('overlaps', $exception->getMessage());
            }
        }

        $this->assertSame(1, AssetMovement::count());
    }

    public function test_trips_that_only_touch_or_belong_to_other_equipment_or_were_disregarded_do_not_clash(): void
    {
        $first = $this->create(out: '2026-11-05 08:00:00', in: '2026-11-05 10:00:00');

        // Starts exactly when the first one ended, and a different piece of equipment can be out at the same time.
        $this->create(out: '2026-11-05 10:00:00', in: '2026-11-05 11:00:00');
        $this->create($this->makeEquipment(), out: '2026-11-05 08:30:00');

        $this->assertSame(3, AssetMovement::count());
        $this->assertSame(AssetMovement::STATUS_ACKNOWLEDGED, $first->status);

        // A disregarded trip does not count: a trip left open by mistake is disregarded, then the real one is entered.
        $mistake = $this->create($this->makeEquipment(), out: '2026-11-05 07:00:00');
        $mistake->disregard($this->manager->user_id, 'entered by mistake');
        $this->create($mistake->asset, out: '2026-11-05 09:00:00', in: '2026-11-05 09:30:00');

        $this->assertSame(5, AssetMovement::count());
    }

    public function test_a_scanned_time_in_closes_a_trip_that_was_created_by_hand(): void
    {
        $tag = $this->makeTag($this->equipment, 'AAA1');
        $trip = $this->create(out: '2026-11-05 08:00:00');

        $result = app(MovementRecorder::class)->record([$this->scan($tag, 'IN', '2026-11-05 11:00:00')]);

        $this->assertSame(1, $result['closed']);
        $trip->refresh();
        $this->assertSame('2026-11-05 11:00:00', $trip->time_in->format('Y-m-d H:i:s'));
        $this->assertTrue($trip->isManualOut());
        $this->assertSame(1, AssetMovement::count());
    }

    // ---- the button --------------------------------------------------------------------------------------

    private function list(AppUser $user): Testable
    {
        $this->actingAs($user);

        return Livewire::test(ListAssetMovements::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function form(array $overrides = []): array
    {
        return $overrides + [
            'asset_id' => $this->equipment->getKey(),
            'time_out' => '2026-11-05 11:00:00',
            'yard_out_id' => $this->yard->id,
            'already_back' => false,
            'remarks' => 'No scanner at this yard',
        ];
    }

    public function test_a_manager_can_create_a_trip_from_the_list(): void
    {
        $this->list($this->manager)
            ->callAction('createTrip', $this->form())
            ->assertHasNoActionErrors()
            ->assertNotified('Trip created');

        $trip = AssetMovement::sole();
        $this->assertSame($this->equipment->getKey(), $trip->asset_id);
        $this->assertSame(AssetMovement::STATUS_MISSING_TIME_IN, $trip->status);
    }

    public function test_a_whole_trip_can_be_created_in_one_go_by_someone_who_may_enter_a_time_in(): void
    {
        $this->list($this->admin)
            ->callAction('createTrip', $this->form([
                'time_out' => '2026-11-05 08:00:00',
                'already_back' => true,
                'time_in' => '2026-11-05 10:00:00',
                'yard_in_id' => $this->yard->id,
            ]))
            ->assertHasNoActionErrors();

        $trip = AssetMovement::sole();
        $this->assertSame(AssetMovement::STATUS_ACKNOWLEDGED, $trip->status);
        $this->assertSame($this->admin->user_id, $trip->manual_in_by);
    }

    public function test_entering_a_time_in_follows_the_manual_in_permission(): void
    {
        // Asset admin and super admin have it by default; asset manager does not.
        $this->assertTrue($this->admin->can('manualInAny', AssetMovement::class));
        $this->assertFalse($this->manager->can('manualInAny', AssetMovement::class));
        $this->assertTrue(Role::findByName('super_admin')->hasPermissionTo('ManualIn:AssetMovementResource'));
        $this->assertTrue(Role::findByName('asset_admin')->hasPermissionTo('ManualIn:AssetMovementResource'));
        $this->assertFalse(Role::findByName('asset_manager')->hasPermissionTo('ManualIn:AssetMovementResource'));

        // The manager can still create a trip that is out; a time in sent anyway is not saved.
        $this->list($this->manager)->callAction('createTrip', $this->form([
            'time_out' => '2026-11-05 08:00:00',
            'already_back' => true,
            'time_in' => '2026-11-05 10:00:00',
            'yard_in_id' => $this->yard->id,
        ]));

        $this->assertSame(0, AssetMovement::query()->whereNotNull('time_in')->count());
        $this->assertSame(0, AssetMovement::query()->whereNotNull('manual_in_by')->count());
    }

    public function test_ticking_manual_in_for_another_role_in_shield_lets_it_enter_a_time_in(): void
    {
        Role::findByName('asset_manager')->givePermissionTo('ManualIn:AssetMovementResource');

        $this->list($this->manager)->callAction('createTrip', $this->form([
            'time_out' => '2026-11-05 08:00:00',
            'already_back' => true,
            'time_in' => '2026-11-05 10:00:00',
            'yard_in_id' => $this->yard->id,
        ]));

        $this->assertSame(AssetMovement::STATUS_ACKNOWLEDGED, AssetMovement::sole()->status);
    }

    public function test_a_clashing_trip_is_refused_with_a_message_and_nothing_is_saved(): void
    {
        $this->create(out: '2026-11-05 07:00:00');

        $this->list($this->manager)
            ->callAction('createTrip', $this->form())
            ->assertNotified();

        $this->assertSame(1, AssetMovement::count());
    }

    public function test_without_the_permission_the_button_is_hidden_and_does_nothing(): void
    {
        $this->list($this->viewer)->assertActionHidden('createTrip');

        $this->assertFalse($this->viewer->can('createTrip', AssetMovement::class));
        $this->assertTrue($this->manager->can('createTrip', AssetMovement::class));
        $this->assertSame(0, AssetMovement::count());
    }

    public function test_the_asset_page_creates_a_trip_for_that_asset_only(): void
    {
        $this->actingAs($this->manager);

        Livewire::test(MovementsRelationManager::class, ['ownerRecord' => $this->equipment, 'pageClass' => ViewEquipment::class])
            ->callAction(TestAction::make('createTrip')->table(), [
                'time_out' => '2026-11-05 11:00:00',
                'yard_out_id' => $this->yard->id,
                'already_back' => false,
                'remarks' => 'No scanner at this yard',
            ])
            ->assertHasNoActionErrors();

        $this->assertSame($this->equipment->getKey(), AssetMovement::sole()->asset_id);
    }

    // ---- emails ------------------------------------------------------------------------------------------

    public function test_a_trip_created_by_hand_sends_the_same_emails(): void
    {
        $this->list($this->manager)->callAction('createTrip', $this->form());
        Mail::assertQueuedCount(1);
        Mail::assertQueued(AssetMovementMail::class, fn (AssetMovementMail $mail): bool => $mail->kind === 'out' && $mail->hasTo($this->manager->user_email) && $mail->hasTo($this->admin->user_email));

        $other = $this->makeEquipment();
        $this->list($this->admin)->callAction('createTrip', $this->form([
            'asset_id' => $other->getKey(),
            'time_out' => '2026-11-05 10:00:00',
            'already_back' => true,
            'time_in' => '2026-11-05 11:30:00',
            'yard_in_id' => $this->yard->id,
        ]));
        Mail::assertQueuedCount(3);
        Mail::assertQueued(AssetMovementMail::class, fn (AssetMovementMail $mail): bool => $mail->kind === 'in');
    }

    public function test_an_old_entry_is_recorded_but_not_emailed(): void
    {
        $this->list($this->admin)->callAction('createTrip', $this->form([
            'time_out' => '2026-11-03 08:00:00',
            'already_back' => true,
            'time_in' => '2026-11-03 17:00:00',
            'yard_in_id' => $this->yard->id,
        ]));

        $this->assertSame(1, DB::table('asset_movements')->count());
        Mail::assertNothingQueued();
    }

    // ---- permission --------------------------------------------------------------------------------------

    public function test_the_migration_grants_create_trip_to_the_three_roles_only(): void
    {
        foreach (['super_admin', 'asset_admin', 'asset_manager'] as $role) {
            $this->assertTrue(Role::findByName($role)->hasPermissionTo('CreateTrip:AssetMovementResource'), $role);
        }

        $this->assertFalse(Role::findByName('viewer')->hasPermissionTo('CreateTrip:AssetMovementResource'));
    }
}

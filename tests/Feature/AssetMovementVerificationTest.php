<?php

namespace Tests\Feature;

use App\Filament\Resources\AssetMovements\Pages\ListAssetMovements;
use App\Filament\Resources\AssetMovements\Pages\ViewAssetMovement;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Equipment\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\Equipment\Resources\AssetTags\AssetTagResource;
use App\Models\Activity;
use App\Models\AppUser;
use App\Models\AssetMovement;
use App\Models\AssetTagLog;
use App\Models\Equipment;
use Carbon\Carbon;
use DomainException;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\Activitylog\ActivityLogStatus;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssetMovementVerificationTest extends AssetMovementTestCase
{
    private AppUser $admin;

    private AppUser $manager;

    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-29 12:00:00');

        Schema::create('activity_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->string('subject_id', 64)->nullable();
            $table->string('event')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });

        foreach (['super_admin', 'asset_admin', 'asset_manager'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // The migration that grants the permissions to the two roles is part of what is tested.
        (require base_path('database/migrations/2026_10_29_121723_add_asset_movement_permissions.php'))->up();
        (require base_path('database/migrations/2026_10_29_140855_add_asset_movement_test_log_permission.php'))->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Filament::setCurrentPanel('admin');

        $this->admin = $this->makeUser('Alma', 'Admin');
        $this->admin->assignRole('asset_admin');
        $this->manager = $this->makeUser('Mario', 'Manager');
        $this->manager->assignRole('asset_manager');

        $this->equipment = $this->makeEquipment();
        $this->makeTag($this->equipment, 'AAA1');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * A trip that is still waiting for someone to verify it.
     *
     * @param  'complete'|'missing_in'|'missing_out'|'acknowledged'|'disregarded'  $state
     */
    private function trip(string $state = 'complete'): AssetMovement
    {
        $factory = AssetMovement::factory();

        $factory = match ($state) {
            'missing_in' => $factory->missingTimeIn(),
            'missing_out' => $factory->missingTimeOut(),
            'acknowledged' => $factory->acknowledged($this->admin->user_id),
            'disregarded' => $factory->disregarded($this->admin->user_id),
            default => $factory,
        };

        return $factory->create([
            'asset_id' => $this->equipment->eqm_id,
            'tag_id' => 'AAA1',
            'time_out' => $state === 'missing_out' ? null : '2026-10-20 08:00:00',
            'time_in' => $state === 'missing_in' ? null : '2026-10-20 17:30:00',
        ]);
    }

    private function list(AppUser $user): Testable
    {
        $this->actingAs($user);

        return Livewire::test(ListAssetMovements::class);
    }

    /**
     * The trip page, which (unlike the list's default tab) can show a trip in any state.
     */
    private function page(AppUser $user, AssetMovement $trip): Testable
    {
        $this->actingAs($user);

        return Livewire::test(ViewAssetMovement::class, ['record' => $trip->getKey()]);
    }

    public function test_the_migration_gives_asset_admin_everything_and_asset_manager_everything_but_manual_in(): void
    {
        foreach (['ViewAny', 'View', 'Acknowledge', 'ManualOut', 'ManualIn', 'Disregard'] as $ability) {
            $this->assertTrue($this->admin->can("{$ability}:AssetMovementResource"), "asset_admin lacks {$ability}");
        }

        foreach (['ViewAny', 'View', 'Acknowledge', 'ManualOut', 'Disregard'] as $ability) {
            $this->assertTrue($this->manager->can("{$ability}:AssetMovementResource"), "asset_manager lacks {$ability}");
        }

        $this->assertFalse($this->manager->can('ManualIn:AssetMovementResource'));
    }

    public function test_a_user_without_the_permission_cannot_open_the_screen(): void
    {
        $this->actingAs($this->makeUser('No', 'Role'));

        Livewire::test(ListAssetMovements::class)->assertForbidden();
    }

    public function test_the_list_starts_on_trips_that_need_attention_and_the_tabs_narrow_it_down(): void
    {
        $pending = $this->trip();
        $missingIn = $this->trip('missing_in');
        $missingOut = $this->trip('missing_out');
        $acknowledged = $this->trip('acknowledged');
        $disregarded = $this->trip('disregarded');

        $this->list($this->manager)
            ->assertCanSeeTableRecords([$pending, $missingIn, $missingOut])
            ->assertCanNotSeeTableRecords([$acknowledged, $disregarded])
            ->set('activeTab', 'pending')
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$missingIn, $missingOut])
            ->set('activeTab', 'missing_in')
            ->assertCanSeeTableRecords([$missingIn])
            ->set('activeTab', 'missing_out')
            ->assertCanSeeTableRecords([$missingOut])
            ->set('activeTab', 'acknowledged')
            ->assertCanSeeTableRecords([$acknowledged])
            ->set('activeTab', 'disregarded')
            ->assertCanSeeTableRecords([$disregarded]);
    }

    public function test_the_status_is_worked_out_from_the_columns(): void
    {
        $this->assertSame('pending_acknowledgement', $this->trip()->status);
        $this->assertSame('missing_time_in', $this->trip('missing_in')->status);
        $this->assertSame('missing_time_out', $this->trip('missing_out')->status);
        $this->assertSame('acknowledged', $this->trip('acknowledged')->status);
        $this->assertSame('disregarded', $this->trip('disregarded')->status);
    }

    public function test_a_complete_trip_can_be_acknowledged_with_remarks(): void
    {
        $trip = $this->trip();

        $this->list($this->manager)
            ->callAction(TestAction::make('acknowledge')->table($trip), ['remarks' => 'Matches the gate sheet'])
            ->assertHasNoFormErrors();

        $trip->refresh();
        $this->assertSame('acknowledged', $trip->status);
        $this->assertSame($this->manager->user_id, $trip->acknowledged_by);
        $this->assertSame('Matches the gate sheet', $trip->acknowledgement_remarks);
        $this->assertNotNull($trip->acknowledged_at);
    }

    public function test_acknowledging_needs_remarks(): void
    {
        $trip = $this->trip();

        $this->list($this->manager)
            ->callAction(TestAction::make('acknowledge')->table($trip), ['remarks' => ''])
            ->assertHasFormErrors(['remarks' => 'required']);

        $this->assertNull($trip->fresh()->acknowledged_at);
    }

    public function test_acknowledge_is_only_offered_on_a_complete_trip_that_nobody_has_dealt_with(): void
    {
        foreach (['missing_in', 'missing_out', 'acknowledged', 'disregarded'] as $state) {
            $this->page($this->manager, $this->trip($state))->assertActionHidden('acknowledge');
        }

        $this->page($this->manager, $this->trip())->assertActionVisible('acknowledge');
    }

    public function test_a_manual_out_fills_in_the_missing_time_out_and_counts_as_acknowledged(): void
    {
        $trip = $this->trip('missing_out');

        $this->list($this->manager)
            ->callAction(TestAction::make('manualOut')->table($trip), ['time_out' => '2026-10-20 07:45', 'remarks' => 'Left before the reader was on'])
            ->assertHasNoFormErrors();

        $trip->refresh();
        $this->assertSame('2026-10-20 07:45:00', $trip->time_out->format('Y-m-d H:i:s'));
        $this->assertTrue($trip->isManualOut());
        $this->assertSame($this->manager->user_id, $trip->manual_out_by);
        $this->assertSame('Left before the reader was on', $trip->manual_out_remarks);
        $this->assertNull($trip->out_log_id);
        $this->assertSame('acknowledged', $trip->status);
        $this->assertSame($this->manager->user_id, $trip->acknowledged_by);
        $this->assertNotNull($trip->acknowledged_at);
        $this->assertSame('Left before the reader was on', $trip->acknowledgement_remarks);
    }

    public function test_a_manual_out_cannot_be_later_than_the_time_in(): void
    {
        $trip = $this->trip('missing_out');

        $this->list($this->manager)
            ->callAction(TestAction::make('manualOut')->table($trip), ['time_out' => '2026-10-20 18:00', 'remarks' => 'x'])
            ->assertHasFormErrors(['time_out']);

        $this->assertNull($trip->fresh()->time_out);
    }

    public function test_manual_out_is_only_offered_when_the_time_out_is_missing(): void
    {
        foreach (['complete', 'missing_in', 'acknowledged', 'disregarded'] as $state) {
            $this->page($this->manager, $this->trip($state))->assertActionHidden('manualOut');
        }

        $this->page($this->manager, $this->trip('missing_out'))->assertActionVisible('manualOut');
    }

    public function test_an_asset_admin_can_record_a_manual_in(): void
    {
        $trip = $this->trip('missing_in');

        $this->list($this->admin)
            ->callAction(TestAction::make('manualIn')->table($trip), ['time_in' => '2026-10-20 18:10', 'remarks' => 'Reader was offline'])
            ->assertHasNoFormErrors();

        $trip->refresh();
        $this->assertSame('2026-10-20 18:10:00', $trip->time_in->format('Y-m-d H:i:s'));
        $this->assertTrue($trip->isManualIn());
        $this->assertSame($this->admin->user_id, $trip->manual_in_by);
        $this->assertNull($trip->in_log_id);
        $this->assertSame('acknowledged', $trip->status);
        $this->assertSame($this->admin->user_id, $trip->acknowledged_by);
        $this->assertNotNull($trip->acknowledged_at);
        $this->assertSame('Reader was offline', $trip->acknowledgement_remarks);
    }

    public function test_a_trip_fixed_by_a_manual_action_leaves_the_needs_attention_tab_and_never_waits_for_acknowledgement(): void
    {
        $missingOut = $this->trip('missing_out');
        $missingIn = $this->trip('missing_in');

        $this->list($this->admin)
            ->assertCanSeeTableRecords([$missingOut, $missingIn])
            ->callAction(TestAction::make('manualOut')->table($missingOut), ['time_out' => '2026-10-20 07:45', 'remarks' => 'a'])
            ->callAction(TestAction::make('manualIn')->table($missingIn), ['time_in' => '2026-10-20 18:10', 'remarks' => 'b'])
            ->assertCanNotSeeTableRecords([$missingOut, $missingIn]);

        $this->assertSame(0, AssetMovement::pendingAcknowledgement()->count());
        $this->assertSame(2, AssetMovement::acknowledged()->count());
    }

    public function test_an_asset_manager_cannot_record_a_manual_in(): void
    {
        $trip = $this->trip('missing_in');

        $this->list($this->manager)->assertActionHidden(TestAction::make('manualIn')->table($trip));

        $this->assertFalse($this->manager->can('manualIn', $trip));
        $this->assertNull($trip->fresh()->time_in);
    }

    public function test_a_manual_in_cannot_be_earlier_than_the_time_out_or_in_the_future(): void
    {
        $trip = $this->trip('missing_in');

        $this->list($this->admin)
            ->callAction(TestAction::make('manualIn')->table($trip), ['time_in' => '2026-10-20 07:00', 'remarks' => 'x'])
            ->assertHasFormErrors(['time_in']);

        $this->list($this->admin)
            ->callAction(TestAction::make('manualIn')->table($trip), ['time_in' => '2026-11-02 07:00', 'remarks' => 'x'])
            ->assertHasFormErrors(['time_in']);

        $this->assertNull($trip->fresh()->time_in);
    }

    public function test_manual_in_is_only_offered_when_the_time_in_is_missing(): void
    {
        foreach (['complete', 'missing_out', 'acknowledged', 'disregarded'] as $state) {
            $this->page($this->admin, $this->trip($state))->assertActionHidden('manualIn');
        }

        $this->page($this->admin, $this->trip('missing_in'))->assertActionVisible('manualIn');
    }

    public function test_a_trip_can_be_disregarded_with_a_reason(): void
    {
        $trip = $this->trip('missing_in');

        $this->list($this->manager)
            ->callAction(TestAction::make('disregard')->table($trip), ['remarks' => 'Equipment never left the yard'])
            ->assertHasNoFormErrors();

        $trip->refresh();
        $this->assertSame('disregarded', $trip->status);
        $this->assertSame($this->manager->user_id, $trip->disregarded_by);
        $this->assertSame('Equipment never left the yard', $trip->disregard_remarks);
    }

    public function test_disregarding_needs_a_reason_and_is_not_offered_once_acknowledged_or_disregarded(): void
    {
        $trip = $this->trip();

        $this->list($this->manager)
            ->callAction(TestAction::make('disregard')->table($trip), ['remarks' => ''])
            ->assertHasFormErrors(['remarks' => 'required']);

        $this->assertNull($trip->fresh()->disregarded_at);

        foreach (['acknowledged', 'disregarded'] as $state) {
            $this->page($this->manager, $this->trip($state))->assertActionHidden('disregard');
        }
    }

    public function test_a_user_who_can_only_view_sees_none_of_the_actions(): void
    {
        $viewer = $this->makeUser('View', 'Only');
        $viewer->givePermissionTo(
            Permission::findByName('ViewAny:AssetMovementResource'),
            Permission::findByName('View:AssetMovementResource'),
        );
        $trip = $this->trip();

        $this->list($viewer)
            ->assertCanSeeTableRecords([$trip])
            ->assertActionHidden(TestAction::make('acknowledge')->table($trip))
            ->assertActionHidden(TestAction::make('disregard')->table($trip));
    }

    public function test_the_trip_page_offers_the_same_actions(): void
    {
        $trip = $this->trip();
        $this->actingAs($this->manager);

        Livewire::test(ViewAssetMovement::class, ['record' => $trip->getKey()])
            ->assertActionVisible('acknowledge')
            ->assertActionVisible('disregard')
            ->assertActionHidden('manualIn')
            ->callAction('acknowledge', ['remarks' => 'ok'])
            ->assertHasNoFormErrors();

        $this->assertSame('acknowledged', $trip->fresh()->status);
    }

    public function test_the_equipment_and_the_tag_on_the_trip_page_link_to_their_own_pages(): void
    {
        $trip = $this->trip();
        $superAdmin = $this->makeUser('Super', 'Admin');
        $superAdmin->assignRole('super_admin');

        $equipmentUrl = EquipmentResource::getUrl('view', ['record' => $this->equipment]);
        $tagUrl = AssetTagResource::getUrl(
            'view',
            ['record' => 'AAA1', 'asset' => $this->equipment],
            configuration: EquipmentResource::pageKeyForRecord($this->equipment),
        );

        $this->assertStringContainsString("/{$this->equipment->eqm_id}", $equipmentUrl);
        $this->assertStringContainsString("/{$this->equipment->eqm_id}/tags/AAA1", $tagUrl);

        $this->page($superAdmin, $trip)
            ->assertSeeHtml('href="'.$equipmentUrl.'"')
            ->assertSeeHtml('href="'.$tagUrl.'"');
    }

    public function test_the_equipment_and_tag_are_plain_text_for_someone_who_may_not_open_them(): void
    {
        $trip = $this->trip();
        $equipmentUrl = EquipmentResource::getUrl('view', ['record' => $this->equipment]);

        $this->page($this->manager, $trip)
            ->assertSee($this->equipment->eqm_prc_code)
            ->assertSee('AAA1')
            ->assertDontSeeHtml('href="'.$equipmentUrl.'"')
            ->assertDontSeeHtml('/tags/AAA1');
    }

    public function test_the_test_scan_permission_exists_but_no_role_has_it_until_it_is_ticked(): void
    {
        $this->assertTrue(Permission::where('name', 'AddTestLog:AssetMovementResource')->exists());
        $this->assertFalse($this->admin->can('AddTestLog:AssetMovementResource'));
        $this->assertFalse($this->manager->can('AddTestLog:AssetMovementResource'));

        $this->manager->givePermissionTo('AddTestLog:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($this->manager->fresh()->can('AddTestLog:AssetMovementResource'));
    }

    public function test_the_add_test_scan_button_is_only_shown_to_those_who_have_the_permission(): void
    {
        $superAdmin = $this->makeUser('Super', 'Admin');
        $superAdmin->assignRole('super_admin');

        $this->list($this->manager)->assertActionHidden('addTestLog');
        $this->list($this->admin)->assertActionHidden('addTestLog');
        $this->list($superAdmin)->assertActionVisible('addTestLog');

        $this->manager->givePermissionTo('AddTestLog:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->list($this->manager->fresh())->assertActionVisible('addTestLog');
    }

    public function test_a_test_scan_is_stored_and_paired_into_a_trip_like_a_reader_scan(): void
    {
        $this->manager->givePermissionTo('AddTestLog:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $values = fn (string $status, string $at): array => ['tag_id' => 'AAA1', 'status' => $status, 'detected_at' => $at, 'yard_id' => $this->yard->id];

        $this->list($this->manager->fresh())
            ->callAction('addTestLog', $values('OUT', '2026-10-28 08:00:00'))
            ->assertHasNoFormErrors()
            ->assertNotified('Test scan added');

        $this->assertSame(AssetMovement::STATUS_MISSING_TIME_IN, AssetMovement::sole()->status);

        $this->list($this->manager->fresh())
            ->callAction('addTestLog', $values('IN', '2026-10-28 17:30:00'))
            ->assertHasNoFormErrors();

        $trip = AssetMovement::sole();
        $this->assertSame(AssetMovement::STATUS_PENDING_ACKNOWLEDGEMENT, $trip->status);
        $this->assertSame(2, AssetTagLog::count());
        $this->assertSame($this->yard->id, $trip->yard_in_id);
    }

    public function test_the_same_test_scan_is_not_stored_twice(): void
    {
        $this->manager->givePermissionTo('AddTestLog:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $values = ['tag_id' => 'AAA1', 'status' => 'OUT', 'detected_at' => '2026-10-28 08:00:00', 'yard_id' => $this->yard->id];

        $this->list($this->manager->fresh())->callAction('addTestLog', $values);
        $this->list($this->manager->fresh())->callAction('addTestLog', $values)->assertNotified('That scan already exists');

        $this->assertSame(1, AssetTagLog::count());
    }

    public function test_a_test_scan_cannot_be_in_the_future_or_for_an_unknown_tag(): void
    {
        $this->manager->givePermissionTo('AddTestLog:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->list($this->manager->fresh())
            ->callAction('addTestLog', ['tag_id' => 'AAA1', 'status' => 'OUT', 'detected_at' => '2026-11-05 08:00:00', 'yard_id' => $this->yard->id])
            ->assertHasFormErrors(['detected_at']);

        $this->list($this->manager->fresh())
            ->callAction('addTestLog', ['tag_id' => 'NOPE', 'status' => 'OUT', 'detected_at' => '2026-10-28 08:00:00', 'yard_id' => $this->yard->id])
            ->assertHasFormErrors(['tag_id']);

        $this->assertSame(0, AssetTagLog::count());
    }

    public function test_someone_without_the_permission_cannot_run_the_action(): void
    {
        // Filament refuses to run an action the person is not allowed to see, so nothing can be stored.
        $this->list($this->manager)->assertActionHidden('addTestLog');

        $this->assertFalse($this->manager->can('addTestLog', AssetMovement::class));
        $this->assertSame(0, AssetTagLog::count());
    }

    public function test_an_equipment_page_lists_only_that_equipments_trips(): void
    {
        $mine = $this->trip();
        $other = $this->makeEquipment();
        $this->makeTag($other, 'BBB1');
        $theirs = AssetMovement::factory()->create(['asset_id' => $other->eqm_id, 'tag_id' => 'BBB1']);
        $this->actingAs($this->manager);

        Livewire::test(MovementsRelationManager::class, ['ownerRecord' => $this->equipment, 'pageClass' => ViewEquipment::class])
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs])
            ->assertTableColumnFormattedStateSet('status', 'Pending acknowledgement', $mine);
    }

    public function test_the_trips_tab_can_be_filtered_by_status(): void
    {
        $pending = $this->trip();
        $acknowledged = $this->trip('acknowledged');
        $missingIn = $this->trip('missing_in');
        $this->actingAs($this->manager);

        Livewire::test(MovementsRelationManager::class, ['ownerRecord' => $this->equipment, 'pageClass' => ViewEquipment::class])
            ->filterTable('status', AssetMovement::STATUS_ACKNOWLEDGED)
            ->assertCanSeeTableRecords([$acknowledged])
            ->assertCanNotSeeTableRecords([$pending, $missingIn])
            ->filterTable('status', AssetMovement::STATUS_MISSING_TIME_IN)
            ->assertCanSeeTableRecords([$missingIn])
            ->assertCanNotSeeTableRecords([$pending, $acknowledged]);
    }

    public function test_the_trips_tab_shows_only_for_equipment_and_only_to_those_who_may_see_trips(): void
    {
        $accessory = $this->makeAccessory();
        $this->actingAs($this->manager);

        $this->assertTrue(MovementsRelationManager::canViewForRecord($this->equipment, ViewEquipment::class));
        $this->assertFalse(MovementsRelationManager::canViewForRecord($accessory, ViewEquipment::class));
        $this->assertSame('0', MovementsRelationManager::getBadge($this->equipment, ViewEquipment::class) ?? '0');

        $this->trip();
        $this->trip('missing_in');
        $this->trip('acknowledged');
        $this->assertSame('2', MovementsRelationManager::getBadge($this->equipment, ViewEquipment::class));

        $this->actingAs($this->makeUser('No', 'Role'));
        $this->assertFalse(MovementsRelationManager::canViewForRecord($this->equipment, ViewEquipment::class));
    }

    public function test_several_trips_can_be_acknowledged_at_once_and_the_ones_that_cannot_are_skipped(): void
    {
        $ready = collect([$this->trip(), $this->trip(), $this->trip()]);
        $missingIn = $this->trip('missing_in');
        $missingOut = $this->trip('missing_out');

        $this->list($this->manager)
            ->selectTableRecords([...$ready->pluck('id')->all(), $missingIn->id, $missingOut->id])
            ->callAction(TestAction::make('bulkAcknowledge')->table()->bulk(), ['remarks' => 'Checked against the gate sheet'])
            ->assertHasNoFormErrors()
            ->assertNotified('3 trips acknowledged');

        foreach ($ready as $trip) {
            $trip->refresh();
            $this->assertSame('acknowledged', $trip->status);
            $this->assertSame($this->manager->user_id, $trip->acknowledged_by);
            $this->assertSame('Checked against the gate sheet', $trip->acknowledgement_remarks);
        }

        // A trip missing a time cannot be acknowledged: it stays as it was.
        $this->assertSame('missing_time_in', $missingIn->fresh()->status);
        $this->assertSame('missing_time_out', $missingOut->fresh()->status);
    }

    public function test_several_trips_can_be_disregarded_at_once_and_verified_ones_are_left_alone(): void
    {
        $open = collect([$this->trip('missing_in'), $this->trip('missing_out'), $this->trip()]);
        $acknowledged = $this->trip('acknowledged');

        $this->list($this->manager)
            ->set('activeTab', 'acknowledged')
            ->selectTableRecords([$acknowledged->id])
            ->callAction(TestAction::make('bulkDisregard')->table()->bulk(), ['remarks' => 'x'])
            ->assertNotified('0 trips disregarded');

        $this->assertSame('acknowledged', $acknowledged->fresh()->status);

        $this->list($this->manager)
            ->selectTableRecords($open->pluck('id')->all())
            ->callAction(TestAction::make('bulkDisregard')->table()->bulk(), ['remarks' => 'Not real trips'])
            ->assertNotified('3 trips disregarded');

        foreach ($open as $trip) {
            $trip->refresh();
            $this->assertSame('disregarded', $trip->status);
            $this->assertSame($this->manager->user_id, $trip->disregarded_by);
            $this->assertSame('Not real trips', $trip->disregard_remarks);
        }
    }

    public function test_the_bulk_actions_need_remarks_and_the_matching_permission(): void
    {
        $trip = $this->trip();

        $this->list($this->manager)
            ->selectTableRecords([$trip->id])
            ->callAction(TestAction::make('bulkAcknowledge')->table()->bulk(), ['remarks' => ''])
            ->assertHasFormErrors(['remarks' => 'required']);

        $this->assertNull($trip->fresh()->acknowledged_at);

        // Someone who can only look at trips is not offered either bulk action.
        $viewer = $this->makeUser('View', 'Only');
        $viewer->givePermissionTo('ViewAny:AssetMovementResource', 'View:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->list($viewer->fresh())
            ->assertActionHidden(TestAction::make('bulkAcknowledge')->table()->bulk())
            ->assertActionHidden(TestAction::make('bulkDisregard')->table()->bulk());
    }

    public function test_a_super_admin_gets_the_bulk_actions_too(): void
    {
        $superAdmin = $this->makeUser('Super', 'Admin');
        $superAdmin->assignRole('super_admin');
        $trips = collect([$this->trip(), $this->trip()]);

        $this->list($superAdmin)
            ->selectTableRecords($trips->pluck('id')->all())
            ->assertActionVisible(TestAction::make('bulkDisregard')->table()->bulk())
            ->callAction(TestAction::make('bulkAcknowledge')->table()->bulk(), ['remarks' => 'ok'])
            ->assertNotified('2 trips acknowledged');
    }

    public function test_a_bulk_acknowledge_is_logged_per_trip_and_grouped_as_one_bulk_action(): void
    {
        config(['activitylog.enabled' => true]);
        app(ActivityLogStatus::class)->enable();
        $trips = collect([$this->trip(), $this->trip()]);
        Activity::query()->delete();

        $this->list($this->manager)
            ->selectTableRecords($trips->pluck('id')->all())
            ->callAction(TestAction::make('bulkAcknowledge')->table()->bulk(), ['remarks' => 'ok']);

        $entries = Activity::query()->where('subject_type', AssetMovement::class)->whereIn('subject_id', $trips->pluck('id'))->get();

        $this->assertCount(2, $entries);
        $this->assertSame($this->manager->user_id, $entries->first()->causer_id);
        $this->assertNotNull($entries->first()->batch_uuid);
        $this->assertSame(1, $entries->pluck('batch_uuid')->unique()->count());
    }

    public function test_a_bulk_action_changes_a_chunk_with_one_update_however_many_trips_are_selected(): void
    {
        config(['activitylog.enabled' => true]);
        app(ActivityLogStatus::class)->enable();
        $trips = collect(range(1, 30))->map(fn () => $this->trip());
        $page = $this->list($this->manager)->set('tableRecordsPerPage', 50)->selectTableRecords($trips->pluck('id')->all());
        Activity::query()->delete();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $page->callAction(TestAction::make('bulkAcknowledge')->table()->bulk(), ['remarks' => 'ok'])->assertNotified('30 trips acknowledged');
        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();

        $updates = $queries->filter(fn (string $sql): bool => str_starts_with($sql, 'update "asset_movements"'));
        $reads = $queries->filter(fn (string $sql): bool => str_starts_with($sql, 'select') && str_contains($sql, 'from "asset_movements"'));

        $this->assertCount(1, $updates, 'one UPDATE for the whole chunk, not one per trip');
        // The selected ids and one locked read are the action's; the other two redraw the page (tab counts, table count).
        $this->assertLessThanOrEqual(4, $reads->count(), 'a fixed number of reads, not one per trip: '.$reads->map(fn (string $sql): string => substr($sql, 0, 120))->implode(' | '));
        $this->assertCount(0, $reads->filter(fn (string $sql): bool => str_contains($sql, '"id" = ?')), 'no read of a single trip');
        $this->assertSame(30, Activity::query()->where('subject_type', AssetMovement::class)->count(), 'still one activity entry per trip');
        $this->assertSame(30, AssetMovement::acknowledged()->count());
    }

    public function test_a_selection_bigger_than_a_chunk_is_done_in_chunks_and_counted_correctly(): void
    {
        $trips = collect(range(1, 120))->map(fn () => $this->trip());
        $page = $this->list($this->manager)->set('tableRecordsPerPage', 100)->selectTableRecords($trips->pluck('id')->all());

        DB::flushQueryLog();
        DB::enableQueryLog();
        $page->callAction(TestAction::make('bulkDisregard')->table()->bulk(), ['remarks' => 'Test data'])->assertNotified('120 trips disregarded');
        $updates = collect(DB::getQueryLog())->pluck('query')->filter(fn (string $sql): bool => str_starts_with($sql, 'update "asset_movements"'));
        DB::disableQueryLog();

        $this->assertCount(2, $updates, '120 trips are 2 chunks of at most 100');
        $this->assertSame(120, AssetMovement::disregarded()->count());
    }

    public function test_the_bulk_methods_only_change_trips_that_are_still_eligible(): void
    {
        $ready = $this->trip();
        $missingIn = $this->trip('missing_in');
        $alreadyAcknowledged = $this->trip('acknowledged');
        $ids = [$ready->id, $missingIn->id, $alreadyAcknowledged->id, 99999];

        $this->assertSame(1, AssetMovement::acknowledgeMany($ids, $this->admin->user_id, 'ok'));
        $this->assertSame('acknowledged', $ready->fresh()->status);
        $this->assertSame('missing_time_in', $missingIn->fresh()->status);
        // The acknowledgement already on the other trip is not overwritten.
        $this->assertSame($this->admin->user_id, $alreadyAcknowledged->fresh()->acknowledged_by);
        $this->assertSame('Checked', $alreadyAcknowledged->fresh()->acknowledgement_remarks);

        // Disregard: the open trip goes, the two verified ones stay.
        $this->assertSame(1, AssetMovement::disregardMany($ids, $this->admin->user_id, 'Not real'));
        $this->assertSame('disregarded', $missingIn->fresh()->status);
        $this->assertSame('acknowledged', $ready->fresh()->status);

        $this->assertSame(0, AssetMovement::acknowledgeMany([], $this->admin->user_id, 'ok'));
    }

    public function test_the_bulk_methods_clear_the_cached_trip_counts_once_and_write_readable_log_entries(): void
    {
        config(['activitylog.enabled' => true]);
        app(ActivityLogStatus::class)->enable();
        $trips = collect([$this->trip(), $this->trip()]);
        $this->actingAs($this->manager);

        $this->assertSame(2, AssetMovement::statusCounts()['pending']);

        AssetMovement::acknowledgeMany($trips->pluck('id')->all(), $this->manager->user_id, 'Looks right');

        $this->assertSame(0, AssetMovement::statusCounts()['pending']);
        $this->assertSame(2, AssetMovement::statusCounts()['acknowledged']);

        $entry = Activity::query()->where('subject_type', AssetMovement::class)->latest('id')->first();
        $this->assertSame('updated', $entry->event);
        $this->assertSame($this->manager->user_id, $entry->causer_id);
        $this->assertSame('Looks right', $entry->properties['attributes']['acknowledgement_remarks']);
        $this->assertNull($entry->properties['old']['acknowledgement_remarks']);
        $this->assertStringContainsString('Equipment Trip', $entry->description);
    }

    public function test_the_same_trip_cannot_be_acted_on_twice(): void
    {
        $first = $this->trip();
        $second = AssetMovement::find($first->id);

        $first->acknowledge($this->admin->user_id, 'first');

        $this->expectException(DomainException::class);
        $second->acknowledge($this->manager->user_id, 'second');
    }

    public function test_the_model_refuses_times_that_do_not_make_sense_even_when_called_directly(): void
    {
        $missingOut = $this->trip('missing_out');
        $missingIn = $this->trip('missing_in');

        foreach ([
            fn () => $missingOut->recordManualOut(Carbon::parse('2026-10-20 18:00'), $this->admin->user_id, 'x'),
            fn () => $missingOut->recordManualOut(Carbon::parse('2026-11-01 08:00'), $this->admin->user_id, 'x'),
            fn () => $missingIn->recordManualIn(Carbon::parse('2026-10-20 07:00'), $this->admin->user_id, 'x'),
            fn () => $missingIn->recordManualIn(Carbon::parse('2026-11-01 08:00'), $this->admin->user_id, 'x'),
            fn () => $missingOut->recordManualIn(Carbon::parse('2026-10-20 08:00'), $this->admin->user_id, 'wrong state'),
            fn () => $missingIn->recordManualOut(Carbon::parse('2026-10-20 08:00'), $this->admin->user_id, 'wrong state'),
        ] as $attempt) {
            try {
                $attempt();
                $this->fail('Expected a DomainException.');
            } catch (DomainException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertNull($missingOut->fresh()->time_out);
        $this->assertNull($missingIn->fresh()->time_in);
    }

    public function test_every_action_is_written_to_the_activity_log_with_who_did_it(): void
    {
        // A model saved during setUp already settled the on/off switch, so flip it as well as the config.
        config(['activitylog.enabled' => true]);
        app(ActivityLogStatus::class)->enable();
        $this->actingAs($this->manager);
        $trip = $this->trip();

        Activity::query()->delete();

        $trip->acknowledge($this->manager->user_id, 'Looks right');

        $entry = Activity::query()->where('subject_type', AssetMovement::class)->where('subject_id', $trip->id)->latest('id')->first();

        $this->assertNotNull($entry, Activity::all()->toJson());
        $this->assertSame('updated', $entry->event);
        $this->assertSame($this->manager->user_id, $entry->causer_id);
        $this->assertSame('Looks right', $entry->properties['attributes']['acknowledgement_remarks']);
        $this->assertSame($this->manager->user_id, $entry->properties['attributes']['acknowledged_by']);
    }
}

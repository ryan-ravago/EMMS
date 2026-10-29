<?php

namespace Tests\Feature;

use App\Filament\Resources\AssetMovements\Pages\ListAssetMovements;
use App\Filament\Resources\AssetMovements\Pages\ViewAssetMovement;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Filament\Resources\Equipment\Resources\AssetTags\AssetTagResource;
use App\Models\Activity;
use App\Models\AppUser;
use App\Models\AssetMovement;
use App\Models\Equipment;
use Carbon\Carbon;
use DomainException;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
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

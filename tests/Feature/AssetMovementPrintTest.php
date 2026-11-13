<?php

namespace Tests\Feature;

use App\Filament\Resources\AssetMovements\Pages\ListAssetMovements;
use App\Filament\Resources\AssetMovements\Pages\ViewAssetMovement;
use App\Models\AppUser;
use App\Models\AssetMovement;
use App\Models\Equipment;
use Carbon\Carbon;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssetMovementPrintTest extends AssetMovementTestCase
{
    private AppUser $admin;

    private AppUser $manager;

    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-13 12:00:00');

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

        (require base_path('database/migrations/2026_10_29_121723_add_asset_movement_permissions.php'))->up();
        (require base_path('database/migrations/2026_11_13_090000_add_print_permission_to_asset_movements.php'))->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Filament::setCurrentPanel('admin');

        $this->admin = $this->makeUser('Alma', 'Admin');
        $this->admin->assignRole('asset_admin');
        $this->manager = $this->makeUser('Mario', 'Manager');
        $this->manager->assignRole('asset_manager');

        $this->equipment = $this->makeEquipment();
        $this->makeTag($this->equipment, 'AAA1');
    }

    /**
     * @param  'complete'|'missing_in'|'acknowledged'|'disregarded'  $state
     */
    private function trip(string $state = 'acknowledged'): AssetMovement
    {
        $factory = AssetMovement::factory();

        $factory = match ($state) {
            'missing_in' => $factory->missingTimeIn(),
            'acknowledged' => $factory->acknowledged($this->admin->user_id),
            'disregarded' => $factory->disregarded($this->admin->user_id),
            default => $factory,
        };

        return $factory->create([
            'asset_id' => $this->equipment->eqm_id,
            'tag_id' => 'AAA1',
            'time_out' => '2026-11-10 08:00:00',
            'time_in' => $state === 'missing_in' ? null : '2026-11-10 17:30:00',
        ]);
    }

    public function test_the_migration_grants_print_to_the_three_roles_and_a_shield_permission_exists(): void
    {
        $this->assertTrue(Permission::where('name', 'Print:AssetMovementResource')->exists());
        $this->assertTrue($this->admin->can('Print:AssetMovementResource'));
        $this->assertTrue($this->manager->can('Print:AssetMovementResource'));

        $superAdmin = $this->makeUser('Super', 'Admin');
        $superAdmin->assignRole('super_admin');
        $this->assertTrue($superAdmin->can('print', $this->trip()));

        $this->assertFalse($this->makeUser('No', 'Role')->can('print', $this->trip()));
    }

    public function test_only_an_acknowledged_trip_can_be_printed(): void
    {
        $this->assertTrue($this->trip('acknowledged')->isPrintable());

        foreach (['complete', 'missing_in', 'disregarded'] as $state) {
            $this->assertFalse($this->trip($state)->isPrintable(), "{$state} must not be printable");
        }
    }

    public function test_an_asset_admin_gets_the_trip_as_a_pdf(): void
    {
        $trip = $this->trip();

        $response = $this->actingAs($this->admin)->get(route('asset-movements.print', $trip));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_the_pdf_is_refused_without_the_permission_without_signing_in_and_for_trips_that_are_not_acknowledged(): void
    {
        $trip = $this->trip();

        $this->get(route('asset-movements.print', $trip))->assertForbidden();
        $this->actingAs($this->makeUser('No', 'Role'))->get(route('asset-movements.print', $trip))->assertForbidden();

        foreach (['complete', 'missing_in', 'disregarded'] as $state) {
            $this->actingAs($this->admin)->get(route('asset-movements.print', $this->trip($state)))->assertForbidden();
        }

        $this->actingAs($this->admin)->get(route('asset-movements.print', 999999))->assertNotFound();
    }

    public function test_an_inactive_user_cannot_print_even_with_the_permission(): void
    {
        $this->admin->forceFill(['is_active' => false])->save();

        $this->actingAs($this->admin)->get(route('asset-movements.print', $this->trip()))->assertForbidden();
    }

    public function test_the_print_button_is_on_the_list_rows_and_the_trip_page_for_acknowledged_trips_only(): void
    {
        $acknowledged = $this->trip();
        $pending = $this->trip('complete');

        $this->actingAs($this->admin);

        Livewire::test(ListAssetMovements::class)
            ->set('activeTab', 'acknowledged')
            ->assertActionVisible(TestAction::make('printTrip')->table($acknowledged))
            ->set('activeTab', 'pending')
            ->assertActionHidden(TestAction::make('printTrip')->table($pending));

        Livewire::test(ViewAssetMovement::class, ['record' => $acknowledged->getKey()])->assertActionVisible('printTrip');
        Livewire::test(ViewAssetMovement::class, ['record' => $pending->getKey()])->assertActionHidden('printTrip');
    }

    public function test_the_button_follows_the_shield_permission(): void
    {
        $trip = $this->trip();

        $role = Role::findByName('asset_manager', 'web');

        $role->revokePermissionTo('Print:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->manager->fresh());

        Livewire::test(ViewAssetMovement::class, ['record' => $trip->getKey()])->assertActionHidden('printTrip');

        $role->givePermissionTo('Print:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->manager->fresh());

        Livewire::test(ViewAssetMovement::class, ['record' => $trip->getKey()])->assertActionVisible('printTrip');
    }
}

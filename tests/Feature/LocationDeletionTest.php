<?php

namespace Tests\Feature;

use App\Filament\Resources\Locations\Pages\EditLocation;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Models\AppUser;
use App\Models\AssetEditLog;
use App\Models\Location;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LocationDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
        });

        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('eqm_name');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
        });

        Schema::create('asset_tag_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('tag_id');
            $table->unsignedBigInteger('yard_id')->nullable();
        });

        Schema::create('lifecycle_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('deploy_to_loc_id')->nullable();
        });

        Schema::create('asset_edit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->json('changes');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('logged_at')->nullable();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        Filament::setCurrentPanel('admin');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function references(): array
    {
        return [
            'child location' => ['child', 'child locations'],
            'equipment' => ['equipment', 'equipment / accessories'],
            'RFID tag log' => ['tag_log', 'RFID tag logs'],
            'lifecycle log' => ['lifecycle_log', 'lifecycle logs'],
            'edit log moving an asset away from the location' => ['edit_log_old', 'edit logs'],
            'edit log moving an asset to the location' => ['edit_log_new', 'edit logs'],
        ];
    }

    public function test_reason_for_history_says_logs_are_permanent(): void
    {
        $location = Location::create(['name' => 'Main Yard']);
        $this->addReference($location, 'lifecycle_log');
        $this->addReference($location, 'edit_log_new');

        $this->assertSame(
            '"Main Yard" appears in 1 lifecycle logs, 1 edit logs. Logs are permanent, so this location must be kept.',
            $location->deletionBlockedReason(),
        );
    }

    public function test_reason_for_removable_records_says_to_move_them_first(): void
    {
        $location = Location::create(['name' => 'Main Yard']);
        $this->addReference($location, 'equipment');
        $this->addReference($location, 'child');

        $this->assertSame(
            '"Main Yard" is still used by 1 child locations, 1 equipment / accessories. Move or remove these first.',
            $location->deletionBlockedReason(),
        );
    }

    public function test_reason_mentions_both_history_and_removable_records(): void
    {
        $location = Location::create(['name' => 'Main Yard']);
        $this->addReference($location, 'equipment');
        $this->addReference($location, 'tag_log');

        $this->assertSame(
            '"Main Yard" appears in 1 RFID tag logs. Logs are permanent, so this location must be kept. It is also used by 1 equipment / accessories.',
            $location->deletionBlockedReason(),
        );
    }

    public function test_unused_location_has_no_blocking_reason(): void
    {
        $this->assertNull(Location::create(['name' => 'Empty Yard'])->deletionBlockedReason());
    }

    public function test_edit_logs_about_other_locations_do_not_block(): void
    {
        $location = Location::create(['name' => 'Main Yard']);
        $other = Location::create(['name' => 'Other Yard']);
        $this->addReference($other, 'edit_log_new');

        $this->assertFalse($location->isReferenced());
    }

    public function test_edit_logs_cannot_be_deleted(): void
    {
        $log = AssetEditLog::create(['asset_id' => 1, 'changes' => ['eqm_name' => ['old' => 'A', 'new' => 'B']], 'logged_at' => now()]);

        try {
            $log->delete();
            $this->fail('Deleting an edit log should throw.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('permanent', $exception->getMessage());
        }

        $this->assertSame(1, AssetEditLog::query()->count());
    }

    #[DataProvider('references')]
    public function test_location_referenced_by_any_record_cannot_be_deleted(string $reference, string $label): void
    {
        $location = Location::create(['name' => 'Main Yard']);
        $this->addReference($location, $reference);

        $this->assertTrue($location->isReferenced());
        $this->assertSame("1 {$label}", $location->referenceSummary());

        try {
            $location->delete();
            $this->fail('Deleting a referenced location should throw.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('cannot be deleted', $exception->getMessage());
        }

        $this->assertTrue(Location::query()->whereKey($location->id)->exists());
    }

    public function test_unreferenced_location_can_be_deleted(): void
    {
        $location = Location::create(['name' => 'Empty Yard']);

        $this->assertFalse($location->isReferenced());
        $this->assertSame('', $location->referenceSummary());

        $location->delete();

        $this->assertFalse(Location::query()->whereKey($location->id)->exists());
    }

    public function test_summary_lists_every_kind_of_reference(): void
    {
        $location = Location::create(['name' => 'Main Yard']);
        $this->addReference($location, 'equipment');
        $this->addReference($location, 'equipment');
        $this->addReference($location, 'lifecycle_log');

        $this->assertSame('2 equipment / accessories, 1 lifecycle logs', $location->referenceSummary());
    }

    public function test_bulk_delete_is_cancelled_when_any_selected_location_is_in_use(): void
    {
        $this->actingAs($this->makeSuperAdmin());
        $used = Location::create(['name' => 'Main Yard']);
        $unused = Location::create(['name' => 'Empty Yard']);
        $this->addReference($used, 'equipment');

        Livewire::test(ListLocations::class)
            ->selectTableRecords([$used->id, $unused->id])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified('Cannot delete locations that are in use');

        $this->assertSame(2, Location::query()->count());
    }

    public function test_bulk_delete_removes_unused_locations(): void
    {
        $this->actingAs($this->makeSuperAdmin());
        $first = Location::create(['name' => 'Yard A']);
        $second = Location::create(['name' => 'Yard B']);

        Livewire::test(ListLocations::class)
            ->selectTableRecords([$first->id, $second->id])
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

        $this->assertSame(0, Location::query()->count());
    }

    public function test_delete_on_edit_page_explains_why_a_location_in_use_cannot_be_deleted(): void
    {
        $this->actingAs($this->makeSuperAdmin());
        $used = Location::create(['name' => 'Main Yard']);
        $this->addReference($used, 'tag_log');

        Livewire::test(EditLocation::class, ['record' => $used->id])
            ->assertActionVisible(DeleteAction::class)
            ->callAction(DeleteAction::class)
            ->assertNotified('Cannot delete this location')
            ->assertNoRedirect();

        $this->assertTrue(Location::query()->whereKey($used->id)->exists());
    }

    public function test_delete_on_edit_page_deletes_an_unused_location(): void
    {
        $this->actingAs($this->makeSuperAdmin());
        $unused = Location::create(['name' => 'Empty Yard']);

        Livewire::test(EditLocation::class, ['record' => $unused->id])
            ->assertActionVisible(DeleteAction::class)
            ->callAction(DeleteAction::class)
            ->assertRedirect();

        $this->assertFalse(Location::query()->whereKey($unused->id)->exists());
    }

    private function addReference(Location $location, string $reference): void
    {
        match ($reference) {
            'child' => Location::create(['name' => 'Child of '.$location->name, 'parent_id' => $location->id]),
            'equipment' => DB::table('equipment_units')->insert(['eqm_name' => 'Unit', 'location_id' => $location->id]),
            'tag_log' => DB::table('asset_tag_logs')->insert(['tag_id' => 'tag-1', 'yard_id' => $location->id]),
            'lifecycle_log' => DB::table('lifecycle_logs')->insert(['asset_id' => 1, 'deploy_to_loc_id' => $location->id]),
            'edit_log_old' => DB::table('asset_edit_logs')->insert(['asset_id' => 1, 'changes' => json_encode(['location_id' => ['old' => $location->id, 'new' => null]])]),
            'edit_log_new' => DB::table('asset_edit_logs')->insert(['asset_id' => 1, 'changes' => json_encode(['location_id' => ['old' => null, 'new' => $location->id]])]),
        };
    }

    private function makeSuperAdmin(): AppUser
    {
        $user = AppUser::create([
            'user_fname' => fake()->firstName(),
            'user_lname' => fake()->lastName(),
            'user_email' => fake()->unique()->safeEmail(),
            'user_dep_id' => 1,
            'is_active' => true,
        ]);

        $user->assignRole('super_admin');

        return $user;
    }
}

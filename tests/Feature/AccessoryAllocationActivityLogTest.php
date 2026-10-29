<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Pages\EditEquipment;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Equipment\RelationManagers\AccessoriesRelationManager;
use App\Models\Action as ModelsAction;
use App\Models\Activity;
use App\Models\AppUser;
use App\Models\AssetType;
use App\Models\Equipment;
use App\Support\Activity\ActivityLabels;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use ReflectionMethod;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AccessoryAllocationActivityLogTest extends TestCase
{
    private Equipment $crane;

    protected function setUp(): void
    {
        parent::setUp();

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();

        Schema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
        });

        Schema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('lifecycle_status_id')->default('idle');
            $table->unsignedBigInteger('eqm_eqmm_id')->nullable();
            $table->string('eqm_name');
            $table->string('eqm_prc_code')->nullable()->unique();
            $table->boolean('eqm_is_active')->default(true);
        });

        Schema::create('lifecycle_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('action_id');
            $table->string('status_id');
            $table->unsignedBigInteger('deploy_to_loc_id')->nullable();
            $table->string('deploy_to_loc_name')->nullable();
            $table->unsignedBigInteger('allocate_to_equipment_id')->nullable();
            $table->string('allocate_to_equipment_name')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->string('performed_by_name')->nullable();
            $table->timestamp('logged_at');
        });

        // Event codes like "alc" are looked up here to get their wording; "allocated" is not one of them.
        Schema::create('actions', function (Blueprint $table): void {
            $table->string('a_id')->primary();
            $table->string('a_present_tense')->nullable();
            $table->string('a_past_tense')->nullable();
            $table->string('a_icon')->nullable();
        });

        Schema::create('statuses', function (Blueprint $table): void {
            $table->string('status_id')->primary();
            $table->string('status_title')->nullable();
            $table->string('status_color')->nullable();
            $table->string('status_icon')->nullable();
        });

        Schema::create('asset_edit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->json('changes');
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('logged_at');
        });

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

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        AssetType::flushIdCache();
        Filament::setCurrentPanel('admin');

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = AppUser::create(['user_fname' => 'Ryan', 'user_lname' => 'Admin', 'user_email' => 'ryan@example.test', 'user_dep_id' => 1, 'is_active' => true]);
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        // Only what the actions below do should end up in the log.
        config(['activitylog.enabled' => false]);
        $this->crane = Equipment::factory()->equipment()->create(['eqm_name' => 'Crawler Crane', 'eqm_prc_code' => 'CC2800-1']);
        config(['activitylog.enabled' => true]);
    }

    protected function tearDown(): void
    {
        AssetType::flushIdCache();

        parent::tearDown();
    }

    private function relationManager(): Testable
    {
        return Livewire::test(AccessoriesRelationManager::class, ['ownerRecord' => $this->crane, 'pageClass' => EditEquipment::class]);
    }

    /**
     * @return Collection<int, Equipment>
     */
    private function accessories(int $count, bool $allocated): Collection
    {
        config(['activitylog.enabled' => false]);
        $made = Equipment::factory()->count($count)->accessory($allocated ? $this->crane : null)->create();
        config(['activitylog.enabled' => true]);

        return $made;
    }

    /**
     * @return Collection<int, Activity>
     */
    private function allocationLogs(string $event): Collection
    {
        return Activity::query()->where('event', $event)->orderBy('id')->get();
    }

    public function test_allocating_an_accessory_is_recorded_in_the_activity_log(): void
    {
        $accessory = $this->accessories(1, allocated: false)->first();

        $this->relationManager()
            ->callAction(TestAction::make(AssociateAction::class)->table(), ['recordId' => [$accessory->eqm_id]])
            ->assertHasNoErrors();

        $this->assertSame($this->crane->eqm_id, $accessory->fresh()->parent_id);

        $log = $this->allocationLogs('allocated')->sole();
        $this->assertSame(Equipment::class, $log->subject_type);
        $this->assertSame((string) $accessory->eqm_id, (string) $log->subject_id);
        $this->assertStringStartsWith('Allocated "'.$accessory->eqm_name.'" to "Crawler Crane"', $log->description);
        $this->assertStringEndsWith('[Action: Allocate accessory]', $log->description, 'the app adds what triggered it');
        $this->assertSame('Crawler Crane', $log->properties['attributes']['parent_equipment']);
        $this->assertNull($log->properties['old']['parent_equipment']);
        $this->assertSame('Ryan Admin', ActivityLabels::userName($log->load('causer')));
        $this->assertNull($log->batch_uuid, 'a single allocation is not a bulk action');
        $this->assertSame('Allocated', ActivityLabels::eventLabel($log->event));
    }

    public function test_allocating_several_accessories_at_once_logs_each_one_in_one_batch(): void
    {
        $accessories = $this->accessories(3, allocated: false);

        $this->relationManager()
            ->callAction(TestAction::make(AssociateAction::class)->table(), ['recordId' => $accessories->pluck('eqm_id')->all()])
            ->assertHasNoErrors();

        $logs = $this->allocationLogs('allocated');

        $this->assertCount(3, $logs);
        $this->assertEqualsCanonicalizing($accessories->pluck('eqm_id')->map(fn ($id): string => (string) $id)->all(), $logs->pluck('subject_id')->map(fn ($id): string => (string) $id)->all());
        $this->assertNotNull($logs->first()->batch_uuid);
        $this->assertCount(1, $logs->pluck('batch_uuid')->unique(), 'all three share one batch');
    }

    public function test_unallocating_one_accessory_is_recorded_in_the_activity_log(): void
    {
        $accessory = $this->accessories(1, allocated: true)->first();

        $this->relationManager()
            ->callAction(TestAction::make(DissociateAction::class)->table($accessory))
            ->assertHasNoErrors();

        $this->assertNull($accessory->fresh()->parent_id);

        $log = $this->allocationLogs('unallocated')->sole();
        $this->assertStringStartsWith('Unallocated "'.$accessory->eqm_name.'" from "Crawler Crane"', $log->description);
        $this->assertStringEndsWith('[Action: Unallocate]', $log->description);
        $this->assertSame('Crawler Crane', $log->properties['old']['parent_equipment']);
        $this->assertNull($log->properties['attributes']['parent_equipment']);
        $this->assertNull($log->batch_uuid);
        $this->assertSame('Unallocated', ActivityLabels::eventLabel($log->event));
    }

    public function test_bulk_unallocation_logs_every_accessory_grouped_as_a_bulk_action(): void
    {
        $accessories = $this->accessories(3, allocated: true);

        $this->relationManager()
            ->selectTableRecords($accessories->pluck('eqm_id')->all())
            ->callAction(TestAction::make(DissociateBulkAction::class)->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(0, Equipment::query()->whereNotNull('parent_id')->count());

        $logs = $this->allocationLogs('unallocated');

        $this->assertCount(3, $logs);
        $this->assertEqualsCanonicalizing($accessories->pluck('eqm_id')->map(fn ($id): string => (string) $id)->all(), $logs->pluck('subject_id')->map(fn ($id): string => (string) $id)->all());
        $this->assertNotNull($logs->first()->batch_uuid);
        $this->assertCount(1, $logs->pluck('batch_uuid')->unique(), 'a bulk action is one batch');
        $this->assertTrue($logs->every(fn (Activity $log): bool => str_contains((string) $log->properties['via'], 'Bulk action')));
    }

    /**
     * Runs what the asset page's Allocate / Set to idle actions do on submit. The page itself is not rendered
     * (it needs most of the asset tables); its redirect and notification afterwards are not under test.
     *
     * @param  array<string, mixed>  $data
     */
    private function runLifecycleTransition(Equipment $accessory, string $actionId, array $data): void
    {
        DB::table('actions')->insert(['a_id' => $actionId, 'a_present_tense' => $actionId, 'a_past_tense' => $actionId, 'a_icon' => 'heroicon-o-bolt']);

        $page = new ViewEquipment;
        $page->record = $accessory;

        $transition = new ReflectionMethod($page, 'saveLifecycleTransition');
        $transition->invoke($page, ModelsAction::query()->findOrFail($actionId), $actionId, $data);
    }

    public function test_allocating_from_the_asset_page_is_recorded_in_the_activity_log(): void
    {
        $accessory = $this->accessories(1, allocated: false)->first();

        $this->runLifecycleTransition($accessory, 'alc', ['allocate_to_equipment_id' => $this->crane->eqm_id, 'remarks' => 'for the job']);

        $this->assertSame($this->crane->eqm_id, $accessory->fresh()->parent_id);

        $log = $this->allocationLogs('allocated')->sole();
        $this->assertStringStartsWith('Allocated "'.$accessory->eqm_name.'" to "Crawler Crane"', $log->description);
        $this->assertSame('Crawler Crane', $log->properties['attributes']['parent_equipment']);
    }

    public function test_setting_an_allocated_accessory_to_idle_is_recorded_as_an_unallocation(): void
    {
        $accessory = $this->accessories(1, allocated: true)->first();

        $this->runLifecycleTransition($accessory, 'sidle', ['location_id' => null, 'remarks' => 'back to the yard']);

        $this->assertNull($accessory->fresh()->parent_id);

        $log = $this->allocationLogs('unallocated')->sole();
        $this->assertStringStartsWith('Unallocated "'.$accessory->eqm_name.'" from "Crawler Crane"', $log->description);
        $this->assertSame('Crawler Crane', $log->properties['old']['parent_equipment']);
    }

    public function test_the_changes_show_the_equipment_by_name_not_by_number(): void
    {
        $accessory = $this->accessories(1, allocated: false)->first();

        $this->relationManager()->callAction(TestAction::make(AssociateAction::class)->table(), ['recordId' => [$accessory->eqm_id]]);

        $changes = ActivityLabels::changes($this->allocationLogs('allocated')->sole());

        $this->assertSame([['field' => 'Parent Equipment', 'old' => '—', 'new' => 'Crawler Crane']], $changes);
    }
}

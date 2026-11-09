<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Models\Action as ModelsAction;
use App\Models\Activity;
use App\Models\AppUser;
use App\Models\AssetType;
use App\Models\Equipment;
use App\Models\LifecycleLog;
use App\Models\Location;
use App\Policies\EquipmentPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ServiceVehicleDeployTest extends TestCase
{
    private AppUser $user;

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

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
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

        DB::table('actions')->insert(['a_id' => 'dep', 'a_present_tense' => 'Deploy', 'a_past_tense' => 'Deployed', 'a_icon' => 'heroicon-o-truck']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        AssetType::flushIdCache();

        $this->user = AppUser::create(['user_fname' => 'Alma', 'user_lname' => 'Admin', 'user_email' => 'alma@example.test', 'user_dep_id' => 1, 'is_active' => true]);
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        AssetType::flushIdCache();

        parent::tearDown();
    }

    private function canDeploy(Equipment $asset, bool $withPermission = true): bool
    {
        if ($withPermission) {
            $this->user->givePermissionTo(Permission::findOrCreate('Deploy:EquipmentResource', 'web'));
        }

        return (new EquipmentPolicy)->deploy($this->user->refresh(), $asset);
    }

    public function test_a_service_vehicle_can_be_deployed_like_equipment(): void
    {
        $vehicle = Equipment::factory()->serviceVehicle()->create(['lifecycle_status_id' => 'idle']);
        $equipment = Equipment::factory()->equipment()->create(['lifecycle_status_id' => 'idle']);

        $this->assertTrue($this->canDeploy($vehicle));
        $this->assertTrue($this->canDeploy($equipment));
    }

    public function test_an_accessory_still_cannot_be_deployed(): void
    {
        $accessory = Equipment::factory()->accessory()->create(['lifecycle_status_id' => 'idle']);

        $this->assertFalse($this->canDeploy($accessory));
    }

    public function test_a_service_vehicle_that_is_already_deployed_cannot_be_deployed_again(): void
    {
        $vehicle = Equipment::factory()->serviceVehicle()->create(['lifecycle_status_id' => 'dep']);

        $this->assertFalse($this->canDeploy($vehicle));
    }

    public function test_deploying_a_service_vehicle_needs_the_deploy_permission(): void
    {
        $vehicle = Equipment::factory()->serviceVehicle()->create(['lifecycle_status_id' => 'idle']);

        $this->assertFalse($this->canDeploy($vehicle, withPermission: false));
    }

    public function test_deploying_a_service_vehicle_updates_its_status_location_lifecycle_log_and_activity_log(): void
    {
        config(['activitylog.enabled' => false]);
        $vehicle = Equipment::factory()->serviceVehicle()->create(['eqm_name' => 'Pickup 01', 'lifecycle_status_id' => 'idle']);
        $site = Location::create(['name' => 'Valenzuela Yard']);
        config(['activitylog.enabled' => true]);

        $page = new ViewEquipment;
        $page->record = $vehicle;

        (new ReflectionMethod($page, 'saveLifecycleTransition'))->invoke(
            $page,
            ModelsAction::query()->findOrFail('dep'),
            'dep',
            ['location_id' => $site->id, 'remarks' => 'Out for delivery'],
        );

        $vehicle->refresh();
        $this->assertSame('dep', $vehicle->lifecycle_status_id);
        $this->assertSame($site->id, $vehicle->location_id);

        $log = LifecycleLog::query()->where('asset_id', $vehicle->eqm_id)->sole();
        $this->assertSame('dep', $log->action_id);
        $this->assertSame('dep', $log->status_id);
        $this->assertSame($site->id, $log->deploy_to_loc_id);
        $this->assertSame('Valenzuela Yard', $log->deploy_to_loc_name);
        $this->assertSame('Out for delivery', $log->remarks);
        $this->assertSame($this->user->getKey(), $log->performed_by);

        $activity = Activity::query()
            ->where('subject_id', (string) $vehicle->eqm_id)
            ->get()
            ->sole(fn (Activity $entry): bool => ($entry->properties['action_id'] ?? null) === 'dep');
        $this->assertSame('dep', $activity->properties['status_id']);
        $this->assertSame($site->id, $activity->properties['location_id']);
    }
}

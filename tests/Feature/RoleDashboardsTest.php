<?php

namespace Tests\Feature;

use App\Filament\Widgets\ApprovalsQueueWidget;
use App\Filament\Widgets\AssetDataQualityStats;
use App\Filament\Widgets\AssetLifecycleChart;
use App\Filament\Widgets\AssetsByTypeStats;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\EquipmentOutNowWidget;
use App\Filament\Widgets\ExecutiveFleetStats;
use App\Filament\Widgets\MyRequestsWidget;
use App\Filament\Widgets\OpenWorkOrdersByDepartmentChart;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RfidHealthStats;
use App\Filament\Widgets\TeamWorkloadWidget;
use App\Filament\Widgets\TripsNeedingAttentionStats;
use App\Filament\Widgets\WorkOrderHealthStats;
use App\Models\AppUser;
use App\Models\AssetMovement;
use App\Models\Equipment;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use ReflectionMethod;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleDashboardsTest extends AssetMovementTestCase
{
    private const MECH = 2;

    private const ELEC = 3;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-29 12:00:00');

        Schema::create('departments', function (Blueprint $table): void {
            $table->id('dep_id');
            $table->string('dep_code');
            $table->string('dep_name');
        });

        Schema::create('statuses', function (Blueprint $table): void {
            $table->string('status_id')->primary();
            $table->string('status_title')->nullable();
            $table->string('status_color')->default('gray');
            $table->string('status_icon')->nullable();
        });

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id('wo_id');
            $table->string('wo_no')->nullable();
            $table->unsignedBigInteger('wo_eqm_id')->nullable();
            $table->unsignedBigInteger('wo_dep_id')->nullable();
            $table->string('wo_title')->nullable();
            $table->string('wo_status_id')->nullable();
            $table->unsignedBigInteger('wo_created_by')->nullable();
            $table->dateTime('wo_created_dt')->nullable();
            $table->dateTime('wo_closed_dt')->nullable();
        });

        Schema::create('work_order_assignments', function (Blueprint $table): void {
            $table->unsignedBigInteger('woa_wo_id');
            $table->unsignedBigInteger('woa_worker_id');
        });

        Schema::create('equipment_categories', function (Blueprint $table): void {
            $table->id('eqmc_id');
            $table->string('eqmc_name')->nullable();
        });

        Schema::create('equipment_unit_category', function (Blueprint $table): void {
            $table->unsignedBigInteger('eqm_id');
            $table->unsignedBigInteger('eqmc_id');
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

        DB::table('departments')->insert([
            ['dep_id' => 1, 'dep_code' => 'IT', 'dep_name' => 'Information Technology'],
            ['dep_id' => self::MECH, 'dep_code' => 'MECH', 'dep_name' => 'Mechanical'],
            ['dep_id' => self::ELEC, 'dep_code' => 'ELEC', 'dep_name' => 'Electrical'],
        ]);

        DB::table('statuses')->insert([
            ['status_id' => 'pndwor', 'status_title' => 'Pending WO Review', 'status_color' => 'primary'],
            ['status_id' => 'pca', 'status_title' => 'Pending Completion Approval', 'status_color' => 'primary'],
            ['status_id' => 'inprog', 'status_title' => 'In-Progress', 'status_color' => 'info'],
            ['status_id' => 'cmp', 'status_title' => 'Completed', 'status_color' => 'success'],
            ['status_id' => 'rej', 'status_title' => 'Rejected', 'status_color' => 'danger'],
        ]);

        foreach (['super_admin', 'asset_admin', 'asset_manager', 'execom', 'manager', 'requestor', 'technician'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // The trip permissions are what let the asset roles see the trip sections.
        (require base_path('database/migrations/2026_10_29_121723_add_asset_movement_permissions.php'))->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Filament::setCurrentPanel('admin');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function userWith(string $role, int $departmentId = self::MECH, string $name = 'Person'): AppUser
    {
        $user = AppUser::create([
            'user_fname' => $name,
            'user_lname' => ucfirst($role),
            'user_email' => strtolower("{$name}.{$role}.".uniqid()).'@example.test',
            'user_dep_id' => $departmentId,
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function workOrder(string $status, int $departmentId = self::MECH, ?AppUser $requester = null, array $extra = []): WorkOrder
    {
        return WorkOrder::query()->create($extra + [
            'wo_no' => 'WO-'.uniqid(),
            'wo_title' => "Fix {$status}",
            'wo_status_id' => $status,
            'wo_dep_id' => $departmentId,
            'wo_created_by' => $requester?->user_id,
            'wo_created_dt' => now()->subHours(3),
        ]);
    }

    private function chartData(string $chart): array
    {
        $instance = new $chart;

        return (new ReflectionMethod($instance, 'getData'))->invoke($instance);
    }

    public function test_every_section_is_shown_to_the_roles_it_was_made_for_and_hidden_from_the_rest(): void
    {
        $expected = [
            'super_admin' => [TripsNeedingAttentionStats::class, AssetsByTypeStats::class, RfidHealthStats::class, AssetLifecycleChart::class, RecentActivityWidget::class, AssetDataQualityStats::class, EquipmentOutNowWidget::class],
            'asset_admin' => [TripsNeedingAttentionStats::class, AssetsByTypeStats::class, RfidHealthStats::class, AssetLifecycleChart::class, AssetDataQualityStats::class, EquipmentOutNowWidget::class],
            'asset_manager' => [TripsNeedingAttentionStats::class, AssetsByTypeStats::class, RfidHealthStats::class, AssetLifecycleChart::class, EquipmentOutNowWidget::class],
            'execom' => [ExecutiveFleetStats::class, WorkOrderHealthStats::class, OpenWorkOrdersByDepartmentChart::class, AssetsByTypeStats::class, AssetLifecycleChart::class],
            'manager' => [ApprovalsQueueWidget::class, TeamWorkloadWidget::class],
            'requestor' => [MyRequestsWidget::class],
        ];
        $all = array_unique(array_merge(...array_values($expected)));

        foreach ($expected as $role => $shown) {
            $this->actingAs($this->userWith($role));

            foreach ($all as $widget) {
                $this->assertSame(in_array($widget, $shown, true), $widget::canView(), "{$widget} for {$role}");
            }
        }
    }

    public function test_the_generic_summary_tiles_are_hidden_from_roles_that_have_their_own_sections(): void
    {
        $this->actingAs($this->userWith('execom'));
        $this->assertFalse(DashboardStatsOverview::canView());

        $this->actingAs($this->userWith('asset_manager'));
        $this->assertFalse(DashboardStatsOverview::canView());

        foreach (['super_admin', 'asset_admin', 'manager', 'requestor', 'technician'] as $role) {
            $this->actingAs($this->userWith($role));
            $this->assertTrue(DashboardStatsOverview::canView(), $role);
        }

        $both = $this->userWith('asset_manager');
        $both->assignRole('manager');
        $this->actingAs($both);
        $this->assertTrue(DashboardStatsOverview::canView(), 'a manager who is also an asset manager keeps the summary');
    }

    public function test_the_trip_sections_need_the_trip_permission_not_just_the_role(): void
    {
        $manager = $this->userWith('asset_manager');
        Role::findByName('asset_manager')->revokePermissionTo('ViewAny:AssetMovementResource');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($manager->fresh());

        $this->assertFalse(TripsNeedingAttentionStats::canView());
        $this->assertFalse(EquipmentOutNowWidget::canView());
    }

    public function test_execom_sees_the_fleet_in_numbers(): void
    {
        DB::table('equipment_units')->insert([
            ['eqm_name' => 'A', 'eqm_prc_code' => 'A', 'eqm_is_active' => true, 'lifecycle_status_id' => 'dep'],
            ['eqm_name' => 'B', 'eqm_prc_code' => 'B', 'eqm_is_active' => true, 'lifecycle_status_id' => 'dep'],
            ['eqm_name' => 'C', 'eqm_prc_code' => 'C', 'eqm_is_active' => false, 'lifecycle_status_id' => 'idle'],
            ['eqm_name' => 'D', 'eqm_prc_code' => 'D', 'eqm_is_active' => true, 'lifecycle_status_id' => 'udmt'],
        ]);
        $this->actingAs($this->userWith('execom'));

        Livewire::test(ExecutiveFleetStats::class)
            ->assertSee('Total assets')
            ->assertSee('3 active, 1 inactive')
            ->assertSee('50% of all assets')
            ->assertSee('25% of all assets');
    }

    public function test_execom_sees_how_work_orders_are_doing(): void
    {
        $this->workOrder('inprog');
        $this->workOrder('pndwor');
        $this->workOrder('pca');
        $this->workOrder('rej');
        // Finished this month, in 6 hours, and one finished last year (not counted).
        $this->workOrder('cmp', extra: ['wo_created_dt' => '2026-10-20 06:00:00', 'wo_closed_dt' => '2026-10-20 12:00:00']);
        $this->workOrder('cmp', extra: ['wo_created_dt' => '2025-01-01 06:00:00', 'wo_closed_dt' => '2025-01-05 06:00:00']);
        $this->actingAs($this->userWith('execom'));

        Livewire::test(WorkOrderHealthStats::class)
            ->assertSee('Open work orders')
            ->assertSee('Waiting on a manager')
            ->assertSee('New requests and completion requests')
            ->assertSee('Completed this month')
            ->assertSee('October 2026')
            ->assertSee('6 hours');
    }

    public function test_the_average_time_to_complete_has_a_placeholder_until_something_is_finished(): void
    {
        $this->actingAs($this->userWith('execom'));

        Livewire::test(WorkOrderHealthStats::class)->assertSee('Average time to complete')->assertSee('—');
    }

    public function test_open_work_orders_are_counted_per_department_biggest_first(): void
    {
        $this->workOrder('inprog', self::MECH);
        $this->workOrder('pndwor', self::ELEC);
        $this->workOrder('pca', self::ELEC);
        $this->workOrder('cmp', self::MECH);
        $this->workOrder('rej', self::MECH);

        $data = $this->chartData(OpenWorkOrdersByDepartmentChart::class);

        $this->assertSame(['Electrical', 'Mechanical'], $data['labels']);
        $this->assertSame([2, 1], $data['datasets'][0]['data']);
    }

    public function test_a_manager_sees_only_their_departments_work_orders_that_wait_for_them_oldest_first(): void
    {
        $mine = $this->workOrder('pndwor', self::MECH, extra: ['wo_created_dt' => now()->subDays(2)]);
        $completion = $this->workOrder('pca', self::MECH, extra: ['wo_created_dt' => now()->subDays(5)]);
        $inProgress = $this->workOrder('inprog', self::MECH);
        $otherDepartment = $this->workOrder('pndwor', self::ELEC);
        $this->actingAs($this->userWith('manager', self::MECH));

        Livewire::test(ApprovalsQueueWidget::class)
            ->assertCanSeeTableRecords([$completion, $mine], inOrder: true)
            ->assertCanNotSeeTableRecords([$inProgress, $otherDepartment]);
    }

    public function test_a_manager_sees_what_each_technician_of_the_department_is_carrying(): void
    {
        $busy = $this->userWith('technician', self::MECH, 'Busy');
        $idle = $this->userWith('technician', self::MECH, 'Idle');
        $elsewhere = $this->userWith('technician', self::ELEC, 'Elsewhere');

        foreach (['inprog', 'inprog', 'pca', 'cmp'] as $status) {
            DB::table('work_order_assignments')->insert(['woa_wo_id' => $this->workOrder($status)->wo_id, 'woa_worker_id' => $busy->user_id]);
        }
        DB::table('work_order_assignments')->insert(['woa_wo_id' => $this->workOrder('inprog', self::ELEC)->wo_id, 'woa_worker_id' => $elsewhere->user_id]);
        $this->actingAs($this->userWith('manager', self::MECH));

        Livewire::test(TeamWorkloadWidget::class)
            ->assertCanSeeTableRecords([$busy, $idle], inOrder: true)
            ->assertCanNotSeeTableRecords([$elsewhere])
            ->assertTableColumnStateSet('open_work_orders_count', 3, $busy)
            ->assertTableColumnStateSet('awaiting_review_count', 1, $busy)
            ->assertTableColumnStateSet('open_work_orders_count', 0, $idle);
    }

    public function test_a_requestor_sees_only_their_own_latest_requests(): void
    {
        $me = $this->userWith('requestor');
        $someoneElse = $this->userWith('requestor');
        $mine = $this->workOrder('rej', requester: $me);
        $theirs = $this->workOrder('inprog', requester: $someoneElse);
        $this->actingAs($me);

        Livewire::test(MyRequestsWidget::class)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs])
            ->assertSee('Rejected');
    }

    public function test_a_requestors_list_is_capped_at_the_eight_latest(): void
    {
        $me = $this->userWith('requestor');
        foreach (range(1, 10) as $n) {
            $this->workOrder('inprog', requester: $me, extra: ['wo_no' => "WO-{$n}", 'wo_created_dt' => now()->subDays(20 - $n)]);
        }
        $this->actingAs($me);

        Livewire::test(MyRequestsWidget::class)
            ->assertSee('WO-10')
            ->assertSee('WO-3')
            ->assertDontSee('WO-2<')
            ->assertDontSee('WO-1<');
    }

    public function test_asset_admin_sees_the_gaps_in_asset_data(): void
    {
        $complete = DB::table('equipment_units')->insertGetId(['eqm_name' => 'Complete', 'eqm_prc_code' => 'C1', 'location_id' => null, 'eqm_eqmm_id' => 5]);
        DB::table('equipment_unit_category')->insert(['eqm_id' => $complete, 'eqmc_id' => 1]);
        // Active, with no location, model or category.
        DB::table('equipment_units')->insert(['eqm_name' => 'Bare', 'eqm_prc_code' => 'B1']);
        // An accessory allocated to equipment sits where its parent is, so it needs no location.
        DB::table('equipment_units')->insert(['eqm_name' => 'Allocated', 'eqm_prc_code' => 'A1', 'parent_id' => $complete, 'eqm_eqmm_id' => 5]);
        // Inactive records are not counted.
        DB::table('equipment_units')->insert(['eqm_name' => 'Retired', 'eqm_prc_code' => 'R1', 'eqm_is_active' => false]);
        $this->actingAs($this->userWith('asset_admin'));

        Livewire::test(AssetDataQualityStats::class)
            ->assertSee('No location')
            ->assertSee('Not allocated and no location set')
            ->assertSee('No model')
            ->assertSee('No category');
    }

    public function test_asset_roles_see_which_equipment_is_out_and_for_how_long(): void
    {
        $equipment = $this->makeEquipment();
        $this->makeTag($equipment, 'AAA1');
        $out = AssetMovement::factory()->missingTimeIn()->create(['asset_id' => $equipment->eqm_id, 'tag_id' => 'AAA1', 'time_out' => now()->subDays(2)]);
        $back = AssetMovement::factory()->create(['asset_id' => $equipment->eqm_id, 'tag_id' => 'AAA1']);
        $this->actingAs($this->userWith('asset_manager'));

        Livewire::test(EquipmentOutNowWidget::class)
            ->assertCanSeeTableRecords([$out])
            ->assertCanNotSeeTableRecords([$back])
            ->assertSee('2 days');
    }

    public function test_the_asset_tiles_only_link_to_lists_the_person_may_open(): void
    {
        $this->makeEquipment();
        Cache::forget(Equipment::ASSET_COUNTS_CACHE_KEY);

        // Execom has no asset-list permission here, so the tiles are numbers without links.
        $this->actingAs($this->userWith('execom'));
        Livewire::test(AssetsByTypeStats::class)
            ->assertSee('Equipment')
            ->assertDontSeeHtml('resources/equipment');

        $this->actingAs($this->userWith('super_admin'));
        Livewire::test(AssetsByTypeStats::class)->assertSeeHtml('equipment');
    }
}

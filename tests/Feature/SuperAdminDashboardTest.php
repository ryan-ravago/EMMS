<?php

namespace Tests\Feature;

use App\Filament\Widgets\AssetLifecycleChart;
use App\Filament\Widgets\AssetsByTypeStats;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RfidHealthStats;
use App\Filament\Widgets\TripsNeedingAttentionStats;
use App\Models\Activity;
use App\Models\AppUser;
use App\Models\AssetMovement;
use App\Models\AssetTag;
use App\Models\AssetTagLog;
use App\Models\Equipment;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use ReflectionMethod;
use Spatie\Permission\Models\Role;

class SuperAdminDashboardTest extends AssetMovementTestCase
{
    private AppUser $superAdmin;

    private AppUser $manager;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-29 12:00:00');

        Schema::create('statuses', function (Blueprint $table): void {
            $table->string('status_id')->primary();
            $table->string('status_title')->nullable();
            $table->string('status_color')->default('gray');
            $table->string('status_icon')->nullable();
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

        Filament::setCurrentPanel('admin');

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'asset_manager', 'guard_name' => 'web']);

        $this->superAdmin = $this->makeUser('Super', 'Admin');
        $this->superAdmin->assignRole('super_admin');
        $this->manager = $this->makeUser('Mario', 'Manager');
        $this->manager->assignRole('asset_manager');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Which role sees which section is covered by RoleDashboardsTest; here: a super admin sees all of these,
     * and the activity feed stays theirs alone.
     */
    public function test_a_super_admin_sees_every_section_and_the_activity_feed_is_theirs_alone(): void
    {
        $widgets = [
            TripsNeedingAttentionStats::class,
            AssetsByTypeStats::class,
            RfidHealthStats::class,
            AssetLifecycleChart::class,
            RecentActivityWidget::class,
        ];

        $this->actingAs($this->superAdmin);
        foreach ($widgets as $widget) {
            $this->assertTrue($widget::canView(), "$widget should be shown to a super admin");
        }

        $this->actingAs($this->manager);
        $this->assertFalse(RecentActivityWidget::canView());
    }

    public function test_the_trip_tiles_show_the_counts_and_link_to_the_matching_tab(): void
    {
        $equipment = $this->makeEquipment();
        $this->makeTag($equipment, 'AAA1');
        $ack = $this->superAdmin->user_id;

        AssetMovement::factory()->count(2)->create(['asset_id' => $equipment->eqm_id, 'tag_id' => 'AAA1']);
        AssetMovement::factory()->count(3)->missingTimeIn()->create(['asset_id' => $equipment->eqm_id, 'tag_id' => 'AAA1']);
        AssetMovement::factory()->missingTimeOut()->create(['asset_id' => $equipment->eqm_id, 'tag_id' => 'AAA1']);
        AssetMovement::factory()->count(4)->acknowledged($ack)->create(['asset_id' => $equipment->eqm_id, 'tag_id' => 'AAA1']);
        AssetMovement::factory()->disregarded($ack)->create(['asset_id' => $equipment->eqm_id, 'tag_id' => 'AAA1']);

        $this->assertSame(
            ['attention' => 6, 'pending' => 2, 'missing_out' => 1, 'missing_in' => 3, 'acknowledged' => 4, 'disregarded' => 1],
            AssetMovement::statusCounts(),
        );

        $this->actingAs($this->superAdmin);

        Livewire::test(TripsNeedingAttentionStats::class)
            ->assertSee('Pending acknowledgement')
            ->assertSee('Missing time out')
            ->assertSee('Missing time in')
            ->assertSeeHtml('tab=pending')
            ->assertSeeHtml('tab=missing_out')
            ->assertSeeHtml('tab=missing_in');
    }

    public function test_asset_counts_per_type_include_the_records_that_have_no_type(): void
    {
        $this->makeEquipment();
        $this->makeEquipment();
        $this->makeAccessory();
        DB::table('equipment_units')->insert(['eqm_name' => 'Untyped', 'asset_type_id' => null]);
        Cache::forget(Equipment::ASSET_COUNTS_CACHE_KEY);
        $this->actingAs($this->superAdmin);

        Livewire::test(AssetsByTypeStats::class)
            ->assertSee('Equipment')
            ->assertSee('Accessories')
            ->assertSee('Service Vehicles')
            ->assertSee('No asset type')
            ->assertSee('Records that still need a type');
    }

    public function test_rfid_health_reports_scans_tags_and_equipment_without_a_tag(): void
    {
        $tagged = $this->makeEquipment();
        $this->makeEquipment();
        $this->makeTag($tagged, 'AAA1');
        AssetTag::query()->create(['tag_id' => 'OLD1', 'asset_parent_id' => $tagged->eqm_id, 'is_deleted' => true]);
        $this->scan(AssetTag::find('AAA1'), 'OUT', '2026-10-29 08:00:00');
        $this->scan(AssetTag::find('AAA1'), 'IN', '2026-10-29 09:30:00');
        $this->scan(AssetTag::find('AAA1'), 'OUT', '2026-10-27 08:00:00');
        $this->actingAs($this->superAdmin);

        $this->assertSame(2, AssetTagLog::where('detected_at', '>=', now()->startOfDay())->count());

        Livewire::test(RfidHealthStats::class)
            ->assertSee('Scans today')
            ->assertSee('Last scan')
            ->assertSee('Oct 29, 2026 09:30 AM')
            ->assertSee('1 retired')
            ->assertSee('Active equipment that cannot be scanned');
    }

    public function test_rfid_health_flags_the_readers_when_nothing_has_ever_been_scanned(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(RfidHealthStats::class)
            ->assertSee('Never')
            ->assertSee('No scans received yet');
    }

    public function test_the_lifecycle_chart_counts_assets_per_status_with_a_name_and_a_colour(): void
    {
        DB::table('statuses')->insert([
            ['status_id' => 'dep', 'status_title' => 'Deployed', 'status_color' => 'success', 'status_icon' => 'x'],
            ['status_id' => 'idle', 'status_title' => 'Idle', 'status_color' => 'warning', 'status_icon' => 'x'],
        ]);
        foreach (['dep', 'dep', 'dep', 'idle', null] as $status) {
            DB::table('equipment_units')
                ->where('eqm_id', $this->makeEquipment()->eqm_id)
                ->update(['lifecycle_status_id' => $status]);
        }

        $chart = new AssetLifecycleChart;
        $data = (new ReflectionMethod($chart, 'getData'))->invoke($chart);

        $this->assertSame(['Deployed', 'Idle', 'No status'], $data['labels']);
        $this->assertSame([3, 1, 1], $data['datasets'][0]['data']);
        $this->assertSame('#10b981', $data['datasets'][0]['backgroundColor'][0]);
        $this->assertSame('#f59e0b', $data['datasets'][0]['backgroundColor'][1]);
        $this->assertSame('#9ca3af', $data['datasets'][0]['backgroundColor'][2]);
    }

    public function test_recent_activity_shows_the_latest_ten_entries_with_who_did_them(): void
    {
        foreach (range(1, 12) as $n) {
            DB::table('activity_log')->insert([
                'description' => "Did thing {$n}",
                'causer_type' => AppUser::class,
                'causer_id' => $this->manager->user_id,
                'created_at' => now()->subMinutes(20 - $n),
                'updated_at' => now(),
            ]);
        }
        $this->actingAs($this->superAdmin);

        Livewire::test(RecentActivityWidget::class)
            ->assertCanSeeTableRecords(Activity::query()->orderByDesc('id')->limit(10)->get())
            ->assertCanNotSeeTableRecords(Activity::query()->whereIn('description', ['Did thing 1', 'Did thing 2'])->get())
            ->assertSee('Did thing 12')
            ->assertSee('Mario Manager');
    }
}

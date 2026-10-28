<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Pages\EditEquipment;
use App\Filament\Resources\Equipment\RelationManagers\TagsRelationManager;
use App\Filament\Resources\Equipment\Resources\AssetTags\Schemas\AssetTagInfolist;
use App\Models\AppUser;
use App\Models\AssetTag;
use App\Models\AssetType;
use App\Models\Equipment;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AssetTagUiTest extends TestCase
{
    private AppUser $admin;

    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_04_24_054941_create_permission_tables.php'))->up();

        DatabaseSchema::create('app_users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('user_fname')->nullable();
            $table->string('user_lname')->nullable();
            $table->string('user_email')->nullable();
            $table->unsignedBigInteger('user_dep_id')->nullable();
            $table->boolean('is_active')->default(true);
        });

        DatabaseSchema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
        });

        DatabaseSchema::create('equipment_units', function (Blueprint $table): void {
            $table->id('eqm_id');
            $table->foreignId('asset_type_id')->nullable()->constrained('asset_types');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('eqm_eqmm_id')->nullable();
            $table->string('eqm_name');
            $table->string('eqm_prc_code')->nullable()->unique();
            $table->boolean('eqm_is_active')->default(true);
        });

        // Same shape as the real table, including the columns that came from AIMS.
        DatabaseSchema::create('asset_tags', function (Blueprint $table): void {
            $table->string('tag_id')->primary();
            $table->unsignedBigInteger('asset_parent_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->dateTime('assigned_date')->nullable();
            $table->dateTime('scanned_date')->nullable();
            $table->unsignedBigInteger('scanned_by')->nullable();
            $table->boolean('is_deleted')->default(false);
        });

        DatabaseSchema::create('asset_tag_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('tag_id');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        AssetType::flushIdCache();
        Filament::setCurrentPanel('admin');

        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->admin = $this->makeUser('Ryan', 'Admin');
        $this->admin->assignRole('super_admin');
        $this->actingAs($this->admin);

        $this->equipment = Equipment::factory()->equipment()->create();
    }

    protected function tearDown(): void
    {
        AssetType::flushIdCache();

        parent::tearDown();
    }

    private function makeUser(string $first, string $last): AppUser
    {
        return AppUser::create([
            'user_fname' => $first,
            'user_lname' => $last,
            'user_email' => strtolower("{$first}.{$last}").'@example.test',
            'user_dep_id' => 6,
            'is_active' => true,
        ]);
    }

    private function relationManager(): Testable
    {
        return Livewire::test(TagsRelationManager::class, ['ownerRecord' => $this->equipment, 'pageClass' => EditEquipment::class]);
    }

    public function test_the_table_shows_who_assigned_each_tag_when_and_its_status(): void
    {
        $janica = $this->makeUser('Janica', 'Murillo');

        AssetTag::query()->insert([
            ['tag_id' => 'TAG-1', 'asset_parent_id' => $this->equipment->eqm_id, 'assigned_by' => $janica->user_id, 'assigned_date' => '2026-07-18 16:50:08', 'scanned_by' => $janica->user_id, 'scanned_date' => '2026-07-18 16:50:09', 'is_deleted' => false],
            ['tag_id' => 'TAG-2', 'asset_parent_id' => $this->equipment->eqm_id, 'assigned_by' => null, 'assigned_date' => null, 'scanned_by' => null, 'scanned_date' => null, 'is_deleted' => true],
        ]);

        $this->relationManager()
            ->assertCanRenderTableColumn('assigned_by')
            ->assertCanRenderTableColumn('assigned_date')
            ->assertCanRenderTableColumn('is_deleted')
            ->assertTableColumnStateSet('assigned_by', 'Janica Murillo', 'TAG-1')
            ->assertTableColumnStateSet('assigned_by', null, 'TAG-2')
            ->assertTableColumnFormattedStateSet('is_deleted', 'Active', 'TAG-1')
            ->assertTableColumnFormattedStateSet('is_deleted', 'Retired', 'TAG-2')
            ->assertTableColumnFormattedStateSet('assigned_date', 'Jul 18, 2026 04:50 PM', 'TAG-1');
    }

    public function test_scanned_columns_exist_but_start_hidden(): void
    {
        $table = $this->relationManager()
            ->assertTableColumnExists('scanned_by')
            ->assertTableColumnExists('scanned_date')
            ->assertTableColumnVisible('assigned_by')
            ->instance()
            ->getTable();

        $this->assertTrue($table->getColumn('scanned_by')->isToggledHiddenByDefault());
        $this->assertTrue($table->getColumn('scanned_date')->isToggledHiddenByDefault());
        $this->assertFalse($table->getColumn('assigned_by')->isToggledHiddenByDefault());
        $this->assertFalse($table->getColumn('assigned_date')->isToggledHiddenByDefault());
    }

    public function test_tags_can_be_searched_by_the_person_who_assigned_them(): void
    {
        $janica = $this->makeUser('Janica', 'Murillo');
        $nole = $this->makeUser('Nole', 'Gonowon');

        AssetTag::query()->insert([
            ['tag_id' => 'TAG-J', 'asset_parent_id' => $this->equipment->eqm_id, 'assigned_by' => $janica->user_id],
            ['tag_id' => 'TAG-N', 'asset_parent_id' => $this->equipment->eqm_id, 'assigned_by' => $nole->user_id],
        ]);

        $this->relationManager()
            ->searchTable('Murillo')
            ->assertCanSeeTableRecords(['TAG-J'])
            ->assertCanNotSeeTableRecords(['TAG-N']);
    }

    public function test_tags_can_be_filtered_by_assigner_and_by_status(): void
    {
        $janica = $this->makeUser('Janica', 'Murillo');

        AssetTag::query()->insert([
            ['tag_id' => 'TAG-J', 'asset_parent_id' => $this->equipment->eqm_id, 'assigned_by' => $janica->user_id, 'is_deleted' => false],
            ['tag_id' => 'TAG-X', 'asset_parent_id' => $this->equipment->eqm_id, 'assigned_by' => null, 'is_deleted' => true],
        ]);

        $this->relationManager()
            ->filterTable('assigned_by', $janica->user_id)
            ->assertCanSeeTableRecords(['TAG-J'])
            ->assertCanNotSeeTableRecords(['TAG-X']);

        $this->relationManager()
            ->filterTable('is_deleted', true)
            ->assertCanSeeTableRecords(['TAG-X'])
            ->assertCanNotSeeTableRecords(['TAG-J']);

        $this->relationManager()
            ->filterTable('is_deleted', false)
            ->assertCanSeeTableRecords(['TAG-J'])
            ->assertCanNotSeeTableRecords(['TAG-X']);
    }

    public function test_tags_added_in_the_app_record_who_added_them_and_when(): void
    {
        $this->relationManager()
            ->callAction(TestAction::make('addTags')->table(), ['tags' => ['NEW-1', 'NEW-2']])
            ->assertHasNoErrors();

        $tags = AssetTag::query()->whereIn('tag_id', ['NEW-1', 'NEW-2'])->get();

        $this->assertCount(2, $tags);
        $this->assertTrue($tags->every(fn (AssetTag $tag): bool => $tag->assigned_by === $this->admin->user_id));
        $this->assertTrue($tags->every(fn (AssetTag $tag): bool => $tag->assigned_date !== null && $tag->assigned_date->isToday()));
        $this->assertTrue($tags->every(fn (AssetTag $tag): bool => $tag->scanned_by === null && $tag->scanned_date === null && ! $tag->is_deleted));
    }

    public function test_a_tag_can_be_retired_and_reactivated_and_keeps_its_logs(): void
    {
        AssetTag::query()->insert(['tag_id' => 'TAG-1', 'asset_parent_id' => $this->equipment->eqm_id]);
        DatabaseSchema::getConnection()->table('asset_tag_logs')->insert(['tag_id' => 'TAG-1']);

        $this->relationManager()
            ->assertTableActionVisible('retire', 'TAG-1')
            ->assertTableActionHidden('reactivate', 'TAG-1')
            ->callAction(TestAction::make('retire')->table('TAG-1'))
            ->assertNotified('Tag retired');

        $tag = AssetTag::query()->find('TAG-1');
        $this->assertTrue($tag->is_deleted);
        $this->assertSame($this->equipment->eqm_id, $tag->asset_parent_id, 'a retired tag stays on its asset');
        $this->assertSame(1, $tag->logs()->count());

        $this->relationManager()
            ->assertTableActionHidden('retire', 'TAG-1')
            ->assertTableActionVisible('reactivate', 'TAG-1')
            ->callAction(TestAction::make('reactivate')->table('TAG-1'))
            ->assertNotified('Tag reactivated');

        $this->assertFalse(AssetTag::query()->find('TAG-1')->is_deleted);
    }

    public function test_selected_tags_can_be_retired_and_reactivated_together(): void
    {
        AssetTag::query()->insert([
            ['tag_id' => 'TAG-1', 'asset_parent_id' => $this->equipment->eqm_id, 'is_deleted' => false],
            ['tag_id' => 'TAG-2', 'asset_parent_id' => $this->equipment->eqm_id, 'is_deleted' => false],
            ['tag_id' => 'TAG-3', 'asset_parent_id' => $this->equipment->eqm_id, 'is_deleted' => true],
        ]);

        $this->relationManager()
            ->selectTableRecords(['TAG-1', 'TAG-2'])
            ->callAction(TestAction::make('retire')->table()->bulk())
            ->assertNotified('Tags retired');

        $this->assertSame(3, AssetTag::query()->where('is_deleted', true)->count());

        $this->relationManager()
            ->selectTableRecords(['TAG-1', 'TAG-3'])
            ->callAction(TestAction::make('reactivate')->table()->bulk())
            ->assertNotified('Tags reactivated');

        $this->assertSame(['TAG-2'], AssetTag::query()->where('is_deleted', true)->pluck('tag_id')->all());
    }

    public function test_only_users_who_may_update_tags_can_retire_them(): void
    {
        $viewer = $this->makeUser('View', 'Only');
        Permission::findOrCreate('View:EquipmentResource', 'web');
        Permission::findOrCreate('ViewAny:AssetTagResource', 'web');
        $viewer->givePermissionTo(['View:EquipmentResource', 'ViewAny:AssetTagResource']);
        $this->actingAs($viewer);

        AssetTag::query()->insert(['tag_id' => 'TAG-1', 'asset_parent_id' => $this->equipment->eqm_id]);

        $this->relationManager()
            ->selectTableRecords(['TAG-1'])
            ->callAction(TestAction::make('retire')->table()->bulk());

        $this->assertFalse(AssetTag::query()->find('TAG-1')->is_deleted);
    }

    public function test_the_tag_detail_shows_the_assignment_and_the_status(): void
    {
        $janica = $this->makeUser('Janica', 'Murillo');
        $nole = $this->makeUser('Nole', 'Gonowon');

        $tag = new AssetTag([
            'tag_id' => 'E2005142050D0272149077D5',
            'asset_parent_id' => $this->equipment->eqm_id,
            'assigned_by' => $janica->user_id,
            'assigned_date' => '2024-07-18 16:50:08',
            'scanned_by' => $nole->user_id,
            'scanned_date' => '2024-07-18 16:55:00',
            'is_deleted' => false,
        ]);

        $host = new class extends Component implements HasSchemas
        {
            use InteractsWithSchemas;

            public function render(): string
            {
                return '';
            }
        };

        $html = AssetTagInfolist::configure(Schema::make($host))->record($tag)->toHtml();

        foreach (['Assigned By', 'Assigned On', 'Scanned By', 'Scanned On', 'Status', 'Janica Murillo', 'Nole Gonowon', 'Jul 18, 2024 04:50 PM', 'Jul 18, 2024 04:55 PM', 'Active', 'E2005142050D0272149077D5'] as $expected) {
            $this->assertStringContainsString($expected, $html, $expected);
        }
    }

    public function test_the_tag_detail_shows_a_dash_for_tags_without_assignment_details(): void
    {
        $host = new class extends Component implements HasSchemas
        {
            use InteractsWithSchemas;

            public function render(): string
            {
                return '';
            }
        };

        $tag = new AssetTag(['tag_id' => 'OLD-1', 'asset_parent_id' => $this->equipment->eqm_id, 'is_deleted' => true]);

        $html = AssetTagInfolist::configure(Schema::make($host))->record($tag)->toHtml();

        $this->assertStringContainsString('Retired', $html);
        $this->assertStringContainsString('—', $html);
    }
}

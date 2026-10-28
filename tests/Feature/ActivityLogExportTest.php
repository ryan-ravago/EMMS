<?php

namespace Tests\Feature;

use App\Filament\Exports\ActivityExporter;
use App\Filament\Pages\ActivityLogPage;
use App\Models\Activity;
use App\Models\AppUser;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ActivityLogExportTest extends TestCase
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
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Filament::setCurrentPanel('admin');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function makeUser(array $permissions = [], bool $superAdmin = false): AppUser
    {
        $user = AppUser::create(['user_fname' => 'Test', 'user_lname' => 'User', 'user_email' => fake()->unique()->safeEmail(), 'user_dep_id' => 1, 'is_active' => true]);

        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        if ($superAdmin) {
            $user->assignRole('super_admin');
        }

        return $user;
    }

    /**
     * @return array<string, string>
     */
    private function row(\Spatie\Activitylog\Models\Activity $activity, bool $withOptionalColumns = true): array
    {
        $columns = collect(ActivityExporter::getColumns())
            ->filter(fn (ExportColumn $column): bool => $withOptionalColumns || $column->isEnabledByDefault())
            ->mapWithKeys(fn (ExportColumn $column): array => [$column->getName() => $column->getLabel()])
            ->all();

        return array_combine(array_values($columns), (new ActivityExporter(new Export, $columns, []))($activity));
    }

    private function log(array $attributes = []): Activity
    {
        return Activity::query()->create($attributes + [
            'description' => 'Updated Asset Tag "#abc" (Is Deleted: No → Yes) [Action: Retire]',
            'event' => 'updated',
            'subject_type' => 'App\Models\AssetTag',
            'subject_id' => 'abc',
            'properties' => ['attributes' => ['is_deleted' => true], 'old' => ['is_deleted' => false], 'subject_label' => '#abc', 'via' => 'Action: Retire'],
        ]);
    }

    public function test_a_row_shows_names_and_labels_instead_of_ids(): void
    {
        $user = AppUser::create(['user_fname' => 'Janica', 'user_lname' => 'Murillo', 'user_email' => 'j@example.test', 'user_dep_id' => 6, 'is_active' => true]);
        $activity = $this->log(['causer_type' => AppUser::class, 'causer_id' => $user->user_id, 'created_at' => '2026-10-28 10:50:49']);

        $row = $this->row($activity);

        $this->assertSame('2026-10-28 10:50:49', $row['When']);
        $this->assertSame('Janica Murillo', $row['User']);
        $this->assertSame('Updated', $row['Action']);
        $this->assertSame('Asset Tag', $row['Record type']);
        $this->assertSame('#abc', $row['Record']);
        $this->assertSame('Action: Retire', $row['Triggered by']);
        $this->assertSame('No', $row['Bulk action']);
        $this->assertStringContainsString('Is Deleted', $row['Changes']);
    }

    public function test_rows_loaded_through_the_package_model_export_too(): void
    {
        // The Activity Log page's table is built on the package's Activity model, not the app's subclass.
        $id = $this->log()->getKey();
        $activity = \Spatie\Activitylog\Models\Activity::query()->findOrFail($id);

        $this->assertNotInstanceOf(Activity::class, $activity);
        $this->assertSame('Updated', $this->row($activity)['Action']);
    }

    public function test_a_log_without_a_user_or_record_still_exports(): void
    {
        $activity = $this->log(['description' => 'Imported 14144 RFID tags from AIMS', 'event' => 'created', 'subject_type' => null, 'subject_id' => null, 'properties' => [], 'batch_uuid' => 'b0b6c9f2-8a52-4c1e-9c3f-0e5d3d1f7a11']);

        $row = $this->row($activity);

        $this->assertSame('System', $row['User']);
        $this->assertSame('', (string) $row['Record type']);
        $this->assertSame('', (string) $row['Record']);
        $this->assertSame('Yes', $row['Bulk action']);
    }

    public function test_cells_starting_with_a_formula_character_are_neutralized(): void
    {
        $activity = $this->log(['description' => '=HYPERLINK("http://evil.test","click")', 'properties' => ['subject_label' => '=cmd|calc', 'via' => '@SUM(A1)']]);

        $row = $this->row($activity);

        foreach (['What happened', 'Record', 'Triggered by'] as $column) {
            $this->assertStringStartsNotWith('=', $row[$column], $column);
            $this->assertStringStartsNotWith('@', $row[$column], $column);
        }
    }

    public function test_the_raw_properties_are_never_exported_and_changes_and_id_are_optional(): void
    {
        $labels = collect(ActivityExporter::getColumns())->map(fn (ExportColumn $c): string => $c->getLabel());
        $defaults = collect(ActivityExporter::getColumns())->filter(fn (ExportColumn $c): bool => $c->isEnabledByDefault())->map(fn (ExportColumn $c): string => $c->getLabel());

        $this->assertFalse($labels->contains('Properties'));
        $this->assertTrue($labels->contains('Changes') && ! $defaults->contains('Changes'));
        $this->assertTrue($labels->contains('Log ID') && ! $defaults->contains('Log ID'));
    }

    public function test_the_export_query_loads_the_user_in_one_go(): void
    {
        $query = ActivityExporter::modifyQuery(Activity::query());

        $this->assertArrayHasKey('causer', $query->getEagerLoads());
    }

    public function test_super_admin_sees_the_preview_and_export_actions_once_the_migration_has_run(): void
    {
        Role::findByName('super_admin')->givePermissionTo(Permission::findOrCreate('View:ActivityLogPage', 'web'));
        (require base_path('database/migrations/2026_10_28_131723_add_activity_log_export_permissions.php'))->up();
        $this->actingAs($this->makeUser(superAdmin: true));

        Livewire::test(ActivityLogPage::class)
            ->assertActionVisible('previewExport')
            ->assertActionVisible('export');
    }

    public function test_each_action_needs_its_own_permission(): void
    {
        $this->actingAs($this->makeUser(['View:ActivityLogPage']));
        Livewire::test(ActivityLogPage::class)
            ->assertActionHidden('previewExport')
            ->assertActionHidden('export');

        $this->actingAs($this->makeUser(['View:ActivityLogPage', 'ExportActivityLog']));
        Livewire::test(ActivityLogPage::class)
            ->assertActionHidden('previewExport')
            ->assertActionVisible('export');

        $this->actingAs($this->makeUser(['View:ActivityLogPage', 'PreviewActivityLog']));
        Livewire::test(ActivityLogPage::class)
            ->assertActionVisible('previewExport')
            ->assertActionHidden('export');
    }

    public function test_the_migration_creates_the_two_permissions_and_gives_them_to_super_admin_only(): void
    {
        Role::create(['name' => 'manager', 'guard_name' => 'web']);

        (require base_path('database/migrations/2026_10_28_131723_add_activity_log_export_permissions.php'))->up();

        $this->assertSame(2, Permission::query()->whereIn('name', ['PreviewActivityLog', 'ExportActivityLog'])->count());
        $this->assertEqualsCanonicalizing(['PreviewActivityLog', 'ExportActivityLog'], Role::findByName('super_admin')->permissions()->pluck('name')->all());
        $this->assertSame(0, Role::findByName('manager')->permissions()->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\AssetTag;
use App\Models\AssetTagLog;
use App\Models\AssetType;
use App\Models\Equipment;
use App\Models\Location;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Builds the tables the equipment trip tests need (the real asset_movements migration on top of hand-made
 * versions of the tables it points at) and small helpers to create equipment, tags and scans.
 */
abstract class AssetMovementTestCase extends TestCase
{
    protected Location $yard;

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
            $table->unsignedBigInteger('parent_id')->nullable();
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
            $table->string('lifecycle_status_id')->nullable();
            $table->unsignedBigInteger('eqm_eqmm_id')->nullable();
            $table->string('eqm_name');
            $table->string('eqm_prc_code')->nullable()->unique();
            $table->boolean('eqm_is_active')->default(true);
        });

        Schema::create('asset_tags', function (Blueprint $table): void {
            $table->string('tag_id')->primary();
            $table->unsignedBigInteger('asset_parent_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->dateTime('assigned_date')->nullable();
            $table->dateTime('scanned_date')->nullable();
            $table->unsignedBigInteger('scanned_by')->nullable();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::create('asset_tag_logs', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('detected_at');
            $table->string('tag_id');
            $table->unsignedBigInteger('yard_id');
            $table->string('status');
            $table->timestamp('received_at')->useCurrent();
            $table->unique(['yard_id', 'tag_id', 'detected_at', 'status'], 'asset_tag_logs_dedup_unique');
        });

        (require base_path('database/migrations/2026_10_29_121720_create_asset_movements_table.php'))->up();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        AssetType::flushIdCache();

        $this->yard = Location::create(['name' => 'Valenzuela Yard']);
    }

    protected function tearDown(): void
    {
        AssetType::flushIdCache();

        parent::tearDown();
    }

    protected function makeUser(string $first = 'Test', string $last = 'User'): AppUser
    {
        return AppUser::create([
            'user_fname' => $first,
            'user_lname' => $last,
            'user_email' => strtolower("{$first}.{$last}").'@example.test',
            'user_dep_id' => 6,
            'is_active' => true,
        ]);
    }

    protected function makeEquipment(): Equipment
    {
        return Equipment::factory()->equipment()->create();
    }

    protected function makeAccessory(): Equipment
    {
        return Equipment::factory()->accessory()->create();
    }

    protected function makeTag(Equipment $asset, string $tagId): AssetTag
    {
        return AssetTag::create(['tag_id' => $tagId, 'asset_parent_id' => $asset->eqm_id]);
    }

    protected function scan(AssetTag $tag, string $status, string $detectedAt): AssetTagLog
    {
        $log = new AssetTagLog;
        $log->tag_id = $tag->tag_id;
        $log->yard_id = $this->yard->id;
        $log->status = $status;
        $log->detected_at = $detectedAt;
        $log->save();

        return $log;
    }
}

<?php

namespace Tests\Feature;

use App\Models\AssetTag;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class AssetTagDeletionGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        Schema::create('asset_tags', function (Blueprint $table): void {
            $table->string('tag_id')->primary();
            $table->unsignedBigInteger('asset_parent_id')->nullable();
            $table->boolean('is_deleted')->default(false);
        });

        // The log table as it was created: deleting a tag cascaded to its logs. Then the migration under test.
        Schema::create('asset_tag_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('tag_id');
            $table->foreign('tag_id')->references('tag_id')->on('asset_tags')->cascadeOnDelete();
        });

        (require base_path('database/migrations/2026_10_28_112113_protect_asset_tag_logs_from_tag_deletion.php'))->up();

        DB::table('asset_tags')->insert([['tag_id' => 'with-logs'], ['tag_id' => 'no-logs']]);
        DB::table('asset_tag_logs')->insert([['tag_id' => 'with-logs'], ['tag_id' => 'with-logs']]);
    }

    public function test_a_tag_with_logs_cannot_be_deleted_through_the_model(): void
    {
        try {
            AssetTag::query()->findOrFail('with-logs')->delete();
            $this->fail('Deleting a tag that has logs must be refused.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('has scan logs and cannot be deleted', $exception->getMessage());
        }

        $this->assertSame(1, AssetTag::query()->where('tag_id', 'with-logs')->count());
        $this->assertSame(2, DB::table('asset_tag_logs')->count());
    }

    public function test_the_database_refuses_even_a_delete_that_skips_the_model(): void
    {
        try {
            DB::table('asset_tags')->where('tag_id', 'with-logs')->delete();
            $this->fail('The foreign key must refuse the delete.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }

        $this->assertSame(2, DB::table('asset_tag_logs')->count(), 'the logs were not cascaded away');
    }

    public function test_a_tag_without_logs_can_still_be_deleted(): void
    {
        AssetTag::query()->findOrFail('no-logs')->delete();

        $this->assertSame(0, AssetTag::query()->where('tag_id', 'no-logs')->count());
    }

    public function test_logs_can_be_removed_first_and_then_the_tag(): void
    {
        DB::table('asset_tag_logs')->where('tag_id', 'with-logs')->delete();

        AssetTag::query()->findOrFail('with-logs')->delete();

        $this->assertSame(1, AssetTag::query()->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AssetTag;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ActivityLogTextKeyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The table as it was created (subject_id a number), then the migration under test.
        Schema::create('activity_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });

        (require base_path('database/migrations/2026_10_28_105208_allow_text_keys_in_activity_log_subject_id.php'))->up();

        Schema::create('asset_tags', function (Blueprint $table): void {
            $table->string('tag_id')->primary();
            $table->unsignedBigInteger('asset_parent_id')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->dateTime('assigned_date')->nullable();
            $table->dateTime('scanned_date')->nullable();
            $table->unsignedBigInteger('scanned_by')->nullable();
            $table->boolean('is_deleted')->default(false);
        });

        config(['activitylog.enabled' => true]);
    }

    public function test_changing_a_record_with_a_text_key_is_logged_without_error(): void
    {
        $tag = AssetTag::query()->create(['tag_id' => 'e200001d24010082039036ed', 'asset_parent_id' => 7]);

        $tag->update(['is_deleted' => true]);

        $activity = Activity::query()->where('event', 'updated')->latest('id')->firstOrFail();

        $this->assertSame('e200001d24010082039036ed', (string) $activity->subject_id);
        $this->assertSame(AssetTag::class, $activity->subject_type);
        $this->assertTrue($activity->properties['attributes']['is_deleted']);
        $this->assertTrue($activity->subject->is($tag), 'the log row still resolves back to the tag');
    }

    public function test_deleting_a_record_with_a_text_key_is_logged_without_error(): void
    {
        $tag = AssetTag::query()->create(['tag_id' => 'abc123', 'asset_parent_id' => 7]);

        $tag->delete();

        $this->assertSame(1, Activity::query()->where('event', 'deleted')->where('subject_id', 'abc123')->count());
    }
}

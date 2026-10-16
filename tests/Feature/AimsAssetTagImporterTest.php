<?php

namespace Tests\Feature;

use App\Services\Aims\AimsAssetTagImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AimsAssetTagImporterTest extends TestCase
{
    private AimsAssetTagImporter $importer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importer = new AimsAssetTagImporter;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return Collection<int, object>
     */
    private function rows(array $rows): Collection
    {
        return collect($rows)->map(fn (array $row) => (object) $row);
    }

    /**
     * @return array{rows: array<string, int>, skipped: list<array<string, mixed>>, duplicates: int, from_accessories: int}
     */
    private function plan(array $tags, array $parents = [], array $accessories = [], array $units = []): array
    {
        return $this->importer->plan(
            $this->rows($tags),
            $this->rows($parents),
            $this->rows($accessories),
            $units,
        );
    }

    private function tag(int $id, string $rfid, int $assignedTo): array
    {
        return ['ID' => $id, 'RFID' => $rfid, 'ASSIGNED_TO' => $assignedTo];
    }

    private function parent(int $id, string $name, ?int $deleted = null): array
    {
        return ['ID' => $id, 'NAME' => $name, 'IS_DELETE' => $deleted];
    }

    public function test_tag_assigned_to_parent_maps_to_unit_by_prc_code_ignoring_case_and_spaces(): void
    {
        $plan = $this->plan(
            [$this->tag(1, 'E2800ABC', 10)],
            [$this->parent(10, 'SS500 #1')],
            [],
            ['ss500#1' => [7]],
        );

        $this->assertSame(['e2800abc' => 7], $plan['rows']);
        $this->assertSame([], $plan['skipped']);
        $this->assertSame(0, $plan['from_accessories']);
    }

    public function test_tag_assigned_to_accessory_attaches_to_the_accessorys_parent_unit(): void
    {
        $plan = $this->plan(
            [$this->tag(1, 'AAA1', 500)],
            [$this->parent(10, 'KR45H#1')],
            [['ID' => 500, 'PARENT_ID' => 10, 'IS_DELETE' => null]],
            ['kr45h#1' => [3]],
        );

        $this->assertSame(['aaa1' => 3], $plan['rows']);
        $this->assertSame(1, $plan['from_accessories']);
    }

    public function test_unknown_assignment_is_skipped_as_orphan(): void
    {
        $plan = $this->plan([$this->tag(1, 'AAA1', 999)], [$this->parent(10, 'X')], [], ['x' => [1]]);

        $this->assertSame([], $plan['rows']);
        $this->assertSame(AimsAssetTagImporter::SKIP_NO_ASSET, $plan['skipped'][0]['reason']);
    }

    public function test_deleted_parent_and_deleted_accessory_are_skipped(): void
    {
        $plan = $this->plan(
            [$this->tag(1, 'AAA1', 10), $this->tag(2, 'AAA2', 500)],
            [$this->parent(10, 'X', 1), $this->parent(11, 'Y')],
            [['ID' => 500, 'PARENT_ID' => 11, 'IS_DELETE' => 1]],
            ['x' => [1], 'y' => [2]],
        );

        $this->assertSame([], $plan['rows']);
        $this->assertSame(
            [AimsAssetTagImporter::SKIP_ASSET_DELETED, AimsAssetTagImporter::SKIP_ASSET_DELETED],
            array_column($plan['skipped'], 'reason'),
        );
    }

    public function test_parent_without_matching_unit_is_skipped(): void
    {
        $plan = $this->plan([$this->tag(1, 'AAA1', 10)], [$this->parent(10, 'NOPE #1')], [], ['x' => [1]]);

        $this->assertSame(AimsAssetTagImporter::SKIP_NO_UNIT, $plan['skipped'][0]['reason']);
    }

    public function test_ambiguous_prc_code_is_skipped_never_guessed(): void
    {
        $plan = $this->plan([$this->tag(1, 'AAA1', 10)], [$this->parent(10, 'DUP')], [], ['dup' => [1, 2]]);

        $this->assertSame([], $plan['rows']);
        $this->assertSame(AimsAssetTagImporter::SKIP_AMBIGUOUS_UNIT, $plan['skipped'][0]['reason']);
    }

    public function test_exact_duplicate_rfid_is_merged_and_counted(): void
    {
        $plan = $this->plan(
            [$this->tag(1, 'DUPE', 10), $this->tag(2, 'dupe', 10)],
            [$this->parent(10, 'X')],
            [],
            ['x' => [4]],
        );

        $this->assertSame(['dupe' => 4], $plan['rows']);
        $this->assertSame(1, $plan['duplicates']);
        $this->assertSame([], $plan['skipped']);
    }

    public function test_same_rfid_on_two_different_units_is_dropped_and_reported_for_both_rows(): void
    {
        $plan = $this->plan(
            [$this->tag(1, 'CLASH', 10), $this->tag(2, 'CLASH', 11)],
            [$this->parent(10, 'A'), $this->parent(11, 'B')],
            [],
            ['a' => [1], 'b' => [2]],
        );

        $this->assertSame([], $plan['rows']);
        $this->assertCount(2, $plan['skipped']);
        $this->assertSame([1, 2], array_column($plan['skipped'], 'aims_id'));
    }

    public function test_blank_rfid_is_skipped(): void
    {
        $plan = $this->plan([$this->tag(1, '   ', 10)], [$this->parent(10, 'X')], [], ['x' => [1]]);

        $this->assertSame(AimsAssetTagImporter::SKIP_BLANK_TAG, $plan['skipped'][0]['reason']);
    }

    public function test_persist_inserts_new_tags_and_leaves_existing_ones_untouched(): void
    {
        Schema::create('asset_tags', function (Blueprint $table): void {
            $table->string('tag_id')->primary();
            $table->unsignedBigInteger('asset_parent_id')->nullable();
        });

        DB::table('asset_tags')->insert(['tag_id' => 'existing', 'asset_parent_id' => 99]);

        $inserted = $this->importer->persist(['existing' => 1, 'fresh' => 2]);

        $this->assertSame(1, $inserted);
        $this->assertSame(99, (int) DB::table('asset_tags')->where('tag_id', 'existing')->value('asset_parent_id'));
        $this->assertSame(2, (int) DB::table('asset_tags')->where('tag_id', 'fresh')->value('asset_parent_id'));
    }
}

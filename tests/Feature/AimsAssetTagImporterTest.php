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
     * @return array{rows: array<string, int>, skipped: list<array<string, mixed>>, duplicates: int, from_accessory_records: int}
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
        $this->assertSame(0, $plan['from_accessory_records']);
    }

    public function test_a_tag_on_an_accessory_that_is_not_an_asset_in_emms_is_skipped_and_never_given_to_the_parent(): void
    {
        $plan = $this->plan(
            [$this->tag(1, 'AAA1', 500)],
            [$this->parent(10, 'KR45H#1')],
            [['ID' => 500, 'PARENT_ID' => 10, 'NAME' => 'JIB', 'IS_DELETE' => null]],
            ['kr45h#1' => [3]],
        );

        $this->assertSame([], $plan['rows'], 'the parent crane must not receive its parts\' tags');
        $this->assertSame(AimsAssetTagImporter::SKIP_ACCESSORY_NOT_ASSET, $plan['skipped'][0]['reason']);
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

    private function createAssetTagsTable(): void
    {
        Schema::create('asset_tags', function (Blueprint $table): void {
            $table->string('tag_id')->primary();
            $table->unsignedBigInteger('asset_parent_id')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->dateTime('assigned_date')->nullable();
            $table->dateTime('scanned_date')->nullable();
            $table->unsignedBigInteger('scanned_by')->nullable();
            $table->boolean('is_deleted')->default(false);
        });
    }

    public function test_persist_inserts_new_tags_and_leaves_existing_ones_untouched(): void
    {
        $this->createAssetTagsTable();

        DB::table('asset_tags')->insert(['tag_id' => 'existing', 'asset_parent_id' => 99]);

        $inserted = $this->importer->persist(['existing' => 1, 'fresh' => 2]);

        $this->assertSame(1, $inserted);
        $this->assertSame(99, (int) DB::table('asset_tags')->where('tag_id', 'existing')->value('asset_parent_id'));
        $this->assertSame(2, (int) DB::table('asset_tags')->where('tag_id', 'fresh')->value('asset_parent_id'));
    }

    public function test_accessory_tag_goes_to_the_accessorys_own_emms_record_when_it_has_one(): void
    {
        $plan = $this->plan(
            [$this->tag(1, 'OWN', 500), $this->tag(2, 'COMPONENT', 501)],
            [$this->parent(10, 'TRANSPORT EQUIPMENT')],
            [
                ['ID' => 500, 'PARENT_ID' => 10, 'NAME' => 'MPP 03', 'IS_DELETE' => null],
                ['ID' => 501, 'PARENT_ID' => 10, 'NAME' => 'JIB', 'IS_DELETE' => null],
            ],
            ['mpp03' => [801], 'transportequipment' => [5]],
        );

        // MPP 03 is an asset of its own and keeps its tag; the JIB is only a part of the crane and is left out.
        $this->assertSame(['own' => 801], $plan['rows']);
        $this->assertSame(1, $plan['from_accessory_records']);
        $this->assertSame([AimsAssetTagImporter::SKIP_ACCESSORY_NOT_ASSET], array_column($plan['skipped'], 'reason'));
    }

    public function test_a_shared_accessory_name_never_takes_the_tags_of_another_cranes_accessory(): void
    {
        $plan = $this->importer->plan(
            $this->rows([$this->tag(1, 'MINE', 501), $this->tag(2, 'OTHER', 502)]),
            $this->rows([$this->parent(10, 'ZAT800#1'), $this->parent(11, 'XGC12000')]),
            $this->rows([
                ['ID' => 501, 'PARENT_ID' => 10, 'NAME' => 'CWT #2 (10T)', 'IS_DELETE' => null],
                ['ID' => 502, 'PARENT_ID' => 11, 'NAME' => 'CWT #2 (10T)', 'IS_DELETE' => null],
            ]),
            ['cwt#2(10t)' => [900], 'zat800#1' => [1], 'xgc12000' => [2]],
            ['cwt #2 (10t)' => 900],
            [],
            [501 => 900],
        );

        // 501 was added to EMMS as its own asset (900). 502 is another crane's part: it never takes 900, and it is
        // not pushed up onto its crane either.
        $this->assertSame(['mine' => 900], $plan['rows']);
        $this->assertSame(1, $plan['from_accessory_records']);
        $this->assertSame(['OTHER'], array_column($plan['skipped'], 'rfid'));
    }

    public function test_an_alias_also_resolves_an_accessory_recorded_twice_in_aims(): void
    {
        $plan = $this->importer->plan(
            $this->rows([$this->tag(1, 'T1', 501), $this->tag(2, 'T2', 502)]),
            $this->rows([$this->parent(10, 'FAYMONVILLE'), $this->parent(11, 'TRANSPORT EQUIPMENT')]),
            $this->rows([
                ['ID' => 501, 'PARENT_ID' => 10, 'NAME' => 'MVB2 01', 'IS_DELETE' => null],
                ['ID' => 502, 'PARENT_ID' => 11, 'NAME' => 'MVB2 01', 'IS_DELETE' => null],
            ]),
            ['mvb201' => [836]],
            ['mvb2  01' => 836],
            ['MVB2 01' => 'MVB2  01'],
        );

        $this->assertSame(['t1' => 836, 't2' => 836], $plan['rows']);
    }

    public function test_an_alias_maps_an_asset_that_has_a_different_code_in_emms(): void
    {
        $plan = $this->importer->plan(
            $this->rows([$this->tag(1, 'TL1', 10)]),
            $this->rows([$this->parent(10, 'KLT80V#1')]),
            collect(),
            ['klt80v01' => [1514]],
            ['klt80v01' => 1514],
            ['KLT80V#1' => 'KLT80V01'],
        );

        $this->assertSame(['tl1' => 1514], $plan['rows']);
    }

    public function test_the_exactly_matching_code_wins_when_several_emms_codes_look_alike(): void
    {
        $plan = $this->importer->plan(
            $this->rows([$this->tag(1, 'X1', 10), $this->tag(2, 'X2', 11)]),
            $this->rows([$this->parent(10, 'XGC55 #1'), $this->parent(11, 'XGC55 # 1')]),
            collect(),
            ['xgc55#1' => [1423, 1424]],
            ['xgc55 #1' => 1423, 'xgc55#1' => 1424],
        );

        $this->assertSame(['x1' => 1423], $plan['rows']);
        $this->assertSame(AimsAssetTagImporter::SKIP_AMBIGUOUS_UNIT, $plan['skipped'][0]['reason']);
    }

    public function test_assignment_details_are_kept_and_aims_users_are_translated_to_emms_users(): void
    {
        $this->createAssetTagsTable();

        $plan = $this->plan(
            [['ID' => 1, 'RFID' => 'E2001', 'ASSIGNED_TO' => 10, 'ASSIGNED_BY' => 19, 'ASSIGNED_DATE' => '2024-07-18 16:50:08.120', 'SCANNED_BY' => 19, 'SCANNED_DATE' => '2024-07-18 16:50:08.273', 'IS_DELETE' => null],
                ['ID' => 2, 'RFID' => 'E2002', 'ASSIGNED_TO' => 10, 'ASSIGNED_BY' => 77, 'ASSIGNED_DATE' => null, 'SCANNED_BY' => 77, 'SCANNED_DATE' => '2020-01-01 08:00:00', 'IS_DELETE' => 1]],
            [$this->parent(10, 'X')],
            [],
            ['x' => [4]],
        );

        $this->importer->persist($plan['rows'], $plan['meta'], [19 => 18]);

        $first = DB::table('asset_tags')->where('tag_id', 'e2001')->first();
        $this->assertSame(18, (int) $first->assigned_by);
        $this->assertSame(18, (int) $first->scanned_by);
        $this->assertSame('2024-07-18 16:50:08', $first->assigned_date);
        $this->assertSame('2024-07-18 16:50:08', $first->scanned_date);
        $this->assertFalse((bool) $first->is_deleted);

        $second = DB::table('asset_tags')->where('tag_id', 'e2002')->first();
        $this->assertNull($second->assigned_by, 'an AIMS user with no EMMS account is stored as empty, never as the AIMS id');
        $this->assertNull($second->assigned_date);
        $this->assertTrue((bool) $second->is_deleted);
    }
}

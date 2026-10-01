<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * asset_tags becomes: tag_id (string, PK, entered from the UI) + asset_parent_id (FK).
     * asset_tag_logs.tag_id now stores that same string.
     */
    public function up(): void
    {
        // 1. Detach the logs FK so asset_tags can be restructured
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
        });

        // 2. Logs: bigint id -> string, then swap the numeric ids for the tag strings
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->string('tag_id')->change();
        });

        DB::statement(<<<'SQL'
            UPDATE asset_tag_logs AS logs
            INNER JOIN asset_tags AS tags ON tags.id = CAST(logs.tag_id AS UNSIGNED)
            SET logs.tag_id = tags.tag
        SQL);

        // 3. asset_tags: drop id + old unique, rename tag -> tag_id, make it the PK
        Schema::table('asset_tags', function (Blueprint $table) {
            $table->dropUnique(['tag']);
        });

        Schema::table('asset_tags', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('asset_tags', function (Blueprint $table) {
            $table->renameColumn('tag', 'tag_id');
        });

        Schema::table('asset_tags', function (Blueprint $table) {
            $table->primary('tag_id');
        });

        // 4. Re-attach the logs FK to the new PK
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->foreign('tag_id')
                ->references('tag_id')
                ->on('asset_tags')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
        });

        Schema::table('asset_tags', function (Blueprint $table) {
            $table->dropPrimary(['tag_id']);
        });

        Schema::table('asset_tags', function (Blueprint $table) {
            $table->renameColumn('tag_id', 'tag');
        });

        Schema::table('asset_tags', function (Blueprint $table) {
            $table->unique('tag');
        });

        Schema::table('asset_tags', function (Blueprint $table) {
            $table->id()->first();
        });

        DB::statement(<<<'SQL'
            UPDATE asset_tag_logs AS logs
            INNER JOIN asset_tags AS tags ON tags.tag = logs.tag_id
            SET logs.tag_id = tags.id
        SQL);

        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id')->change();
        });

        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->foreign('tag_id')
                ->references('id')
                ->on('asset_tags')
                ->cascadeOnDelete();
        });
    }
};

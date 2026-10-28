<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A tag that has scan logs cannot be deleted: the logs are its children. Until now deleting the tag
     * silently deleted every one of its logs (on delete cascade); now the database refuses.
     */
    public function up(): void
    {
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
            $table->foreign('tag_id')->references('tag_id')->on('asset_tags')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_tag_logs', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
            $table->foreign('tag_id')->references('tag_id')->on('asset_tags')->cascadeOnDelete();
        });
    }
};

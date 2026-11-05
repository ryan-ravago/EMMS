<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether a user is online and when they were last active, for super admins (see TrackUserActivity).
     *
     * last_seen_at   the last request of any kind, including the background refreshes of an open page; a user is
     *                online while this is recent.
     * last_active_at the last request that was a person doing something (opening or using a page), not a
     *                background refresh, so an idle open tab does not look like activity.
     */
    public function up(): void
    {
        Schema::table('app_users', function (Blueprint $table): void {
            $table->timestamp('last_seen_at')->nullable()->after('notify_asset_movements');
            $table->timestamp('last_active_at')->nullable()->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('app_users', function (Blueprint $table): void {
            $table->dropColumn(['last_seen_at', 'last_active_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether the person is emailed each time an equipment or service vehicle goes out or comes in. Only asset
     * admins and asset managers can be emailed; each can switch it off for themselves on My Profile.
     */
    public function up(): void
    {
        Schema::table('app_users', function (Blueprint $table): void {
            $table->boolean('notify_asset_movements')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('app_users', function (Blueprint $table): void {
            $table->dropColumn('notify_asset_movements');
        });
    }
};

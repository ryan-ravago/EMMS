<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION = 'CreateTrip:AssetMovementResource';

    /** Roles that start out able to create a trip; any other role gets it by ticking "Create Trip" in Shield. */
    private const ROLES = ['super_admin', 'asset_admin', 'asset_manager'];

    /**
     * The "Create trip" button on Time In / Out: records a time out (and time in) by hand, for a yard that has
     * no scanner. Granted to the roles above; change it per role in Shield.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('role_has_permissions')) {
            return;
        }

        DB::table('permissions')->insertOrIgnore([
            'name' => self::PERMISSION,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permissionId = DB::table('permissions')->where('name', self::PERMISSION)->where('guard_name', 'web')->value('id');

        foreach (self::ROLES as $roleName) {
            $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');

            if ($roleId !== null && $permissionId !== null) {
                DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')->where('name', self::PERMISSION)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

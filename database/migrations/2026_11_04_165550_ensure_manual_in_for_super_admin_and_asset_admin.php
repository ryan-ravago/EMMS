<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION = 'ManualIn:AssetMovementResource';

    /**
     * Manual in (and the time in of "Create trip") is a Shield permission: on by default for super admin and
     * asset admin only. Asset manager does not get it; tick "Manual In" for any other role in Shield.
     * Ticking it for super admin is only so Shield shows it (super admins can do everything anyway).
     */
    private const ROLES = ['super_admin', 'asset_admin'];

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

    /**
     * The permission itself belongs to the original asset movement migration, so only the grants are undone.
     */
    public function down(): void
    {
        // Nothing to undo: the grants are what a role had before or chose in Shield.
    }
};

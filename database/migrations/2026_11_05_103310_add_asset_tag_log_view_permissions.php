<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const RESOURCE = 'AssetTagLogResource';

    private const ABILITIES = ['ViewAny', 'View'];

    /** Roles that start out able to look at the scan logs; change it per role in Shield. */
    private const ROLES = ['super_admin', 'asset_admin', 'asset_manager'];

    /**
     * Adds the permissions to look at the RFID tag logs and grants them to the roles above when they exist
     * (roles are created in Shield, so a missing role is skipped).
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('role_has_permissions')) {
            return;
        }

        foreach (self::ABILITIES as $ability) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $ability.':'.self::RESOURCE,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', array_map(fn (string $ability): string => $ability.':'.self::RESOURCE, self::ABILITIES))
            ->pluck('id');

        foreach (self::ROLES as $roleName) {
            $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');

            if ($roleId === null) {
                continue;
            }

            foreach ($permissionIds as $permissionId) {
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

        DB::table('permissions')
            ->whereIn('name', array_map(fn (string $ability): string => $ability.':'.self::RESOURCE, self::ABILITIES))
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSION = 'ReceiveEmails:AssetMovementResource';

    /**
     * Roles that start out receiving the emails; any other role gets it by ticking "Receive Emails" in Shield.
     * (Super admins are emailed whatever their permissions are; it is ticked for them so Shield shows it.)
     */
    private const ROLES = ['super_admin', 'asset_admin', 'asset_manager'];

    /**
     * Who is emailed on every equipment / service vehicle time out and time in is set in Shield: the roles with this
     * permission (and always the super admins). It is given to asset admin and asset manager here, so they keep
     * receiving the emails; remove it from a role in Shield to stop them.
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

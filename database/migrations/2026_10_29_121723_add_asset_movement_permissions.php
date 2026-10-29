<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const RESOURCE = 'AssetMovementResource';

    /** Everything the verification screen offers; asset_manager gets all of it except ManualIn. */
    private const ABILITIES = ['ViewAny', 'View', 'Acknowledge', 'ManualOut', 'ManualIn', 'Disregard'];

    private const ROLES = [
        'asset_admin' => self::ABILITIES,
        'asset_manager' => ['ViewAny', 'View', 'Acknowledge', 'ManualOut', 'Disregard'],
    ];

    /**
     * Adds the permissions and grants them to the asset_admin and asset_manager roles when those exist
     * (roles are created in Shield, so a missing role is skipped; tick the permissions in Shield for it later).
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

        foreach (self::ROLES as $roleName => $abilities) {
            $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');

            if ($roleId === null) {
                continue;
            }

            $permissionIds = DB::table('permissions')
                ->where('guard_name', 'web')
                ->whereIn('name', array_map(fn (string $ability): string => $ability.':'.self::RESOURCE, $abilities))
                ->pluck('id');

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

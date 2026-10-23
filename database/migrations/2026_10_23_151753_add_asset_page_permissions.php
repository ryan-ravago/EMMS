<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * One Shield permission per asset page (Equipment, Accessories, Service Vehicles).
     *
     * @var list<string>
     */
    private const PERMISSIONS = [
        'ViewEquipmentList:EquipmentResource',
        'ViewAccessoryList:EquipmentResource',
        'ViewServiceVehicleList:EquipmentResource',
    ];

    /**
     * Roles that could open the old asset list keep seeing every asset page; remove the
     * ones they should not have by unticking them in Shield.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('role_has_permissions')) {
            return;
        }

        $now = now();

        foreach (self::PERMISSIONS as $name) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $newIds = DB::table('permissions')->whereIn('name', self::PERMISSIONS)->where('guard_name', 'web')->pluck('id');

        $roleIds = DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('permissions.name', 'ViewAny:EquipmentResource')
            ->pluck('role_has_permissions.role_id');

        foreach ($roleIds as $roleId) {
            foreach ($newIds as $permissionId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')->whereIn('name', self::PERMISSIONS)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

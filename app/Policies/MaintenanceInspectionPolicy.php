<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceInspection;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Maintenance inspections are copied from AppSheet and are read-only in EMMS.
 *
 * Super admins see everything. A manager of a maintenance department (Mechanical, Electrical or Preventive) sees
 * that department's inspections. Any other role needs the Shield permission and is also limited to its own department.
 */
class MaintenanceInspectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return MaintenanceInspection::roleGrantsAccess($authUser)
            || $authUser->can('ViewAny:MaintenanceInspectionResource');
    }

    public function view(AuthUser $authUser, MaintenanceInspection $maintenanceInspection): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        $allowed = MaintenanceInspection::roleGrantsAccess($authUser)
            || $authUser->can('View:MaintenanceInspectionResource');

        return $allowed && $authUser->department?->dep_code === $maintenanceInspection->department_code;
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, MaintenanceInspection $maintenanceInspection): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, MaintenanceInspection $maintenanceInspection): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, MaintenanceInspection $maintenanceInspection): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, MaintenanceInspection $maintenanceInspection): bool
    {
        return false;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function replicate(AuthUser $authUser, MaintenanceInspection $maintenanceInspection): bool
    {
        return false;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return false;
    }
}

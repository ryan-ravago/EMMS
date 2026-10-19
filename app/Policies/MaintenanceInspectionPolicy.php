<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceInspection;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Maintenance inspections are copied from AppSheet and are read-only in EMMS.
 */
class MaintenanceInspectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MaintenanceInspectionResource');
    }

    public function view(AuthUser $authUser, MaintenanceInspection $maintenanceInspection): bool
    {
        return $authUser->can('View:MaintenanceInspectionResource');
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

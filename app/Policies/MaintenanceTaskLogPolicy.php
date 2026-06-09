<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceTaskLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MaintenanceTaskLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MaintenanceTaskLogResource');
    }

    public function view(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return $authUser->can('View:MaintenanceTaskLogResource');
        }

        $maintenanceTask = $maintenanceTaskLog->maintenanceTask;
        if ($maintenanceTask && $authUser->user_dep_id === $maintenanceTask->mt_dep_id) {
            return $authUser->can('View:MaintenanceTaskLogResource');
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MaintenanceTaskLogResource');
    }

    public function update(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        return $authUser->can('Update:MaintenanceTaskLogResource');
    }

    public function delete(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        return $authUser->can('Delete:MaintenanceTaskLogResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MaintenanceTaskLogResource');
    }

    public function restore(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        return $authUser->can('Restore:MaintenanceTaskLogResource');
    }

    public function forceDelete(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        return $authUser->can('ForceDelete:MaintenanceTaskLogResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MaintenanceTaskLogResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MaintenanceTaskLogResource');
    }

    public function replicate(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        return $authUser->can('Replicate:MaintenanceTaskLogResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MaintenanceTaskLogResource');
    }
}

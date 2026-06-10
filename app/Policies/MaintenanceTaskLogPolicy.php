<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MaintenanceTaskLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MaintenanceTaskLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user is authorized to access maintenance task logs.
     * Only super_admin and managers with the Preventive (PREV) department are allowed.
     */
    private function isAuthorizedUser(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->hasRole('manager') && $authUser->department?->dep_code === 'PREV';
    }

    public function viewAny(AuthUser $authUser): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('ViewAny:MaintenanceTaskLogResource');
    }

    public function view(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return $authUser->can('View:MaintenanceTaskLogResource');
        }

        if ($authUser->hasRole('manager') && $authUser->department?->dep_code === 'PREV') {
            $maintenanceTask = $maintenanceTaskLog->maintenanceTask;
            if ($maintenanceTask && $authUser->user_dep_id === $maintenanceTask->mt_dep_id) {
                return $authUser->can('View:MaintenanceTaskLogResource');
            }
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('Create:MaintenanceTaskLogResource');
    }

    public function update(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('Update:MaintenanceTaskLogResource');
    }

    public function delete(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('Delete:MaintenanceTaskLogResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('DeleteAny:MaintenanceTaskLogResource');
    }

    public function restore(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('Restore:MaintenanceTaskLogResource');
    }

    public function forceDelete(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('ForceDelete:MaintenanceTaskLogResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('ForceDeleteAny:MaintenanceTaskLogResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('RestoreAny:MaintenanceTaskLogResource');
    }

    public function replicate(AuthUser $authUser, MaintenanceTaskLog $maintenanceTaskLog): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('Replicate:MaintenanceTaskLogResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if (! $this->isAuthorizedUser($authUser)) {
            return false;
        }

        return $authUser->can('Reorder:MaintenanceTaskLogResource');
    }
}

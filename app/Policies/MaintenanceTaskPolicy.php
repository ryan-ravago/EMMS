<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MaintenanceTask;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenanceTaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MaintenanceTaskResource');
    }

    public function view(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return $authUser->can('View:MaintenanceTaskResource');
        }

        if ($authUser->hasRole('manager') && $authUser->user_dep_id === $maintenanceTask->mt_dep_id) {
            return $authUser->can('View:MaintenanceTaskResource');
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MaintenanceTaskResource');
    }

    public function update(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        return $authUser->can('Update:MaintenanceTaskResource');
    }

    public function delete(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        return $authUser->can('Delete:MaintenanceTaskResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MaintenanceTaskResource');
    }

    public function restore(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        return $authUser->can('Restore:MaintenanceTaskResource');
    }

    public function forceDelete(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        return $authUser->can('ForceDelete:MaintenanceTaskResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MaintenanceTaskResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MaintenanceTaskResource');
    }

    public function replicate(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        return $authUser->can('Replicate:MaintenanceTaskResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MaintenanceTaskResource');
    }

    public function makeWorkOrder(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        if (
            $authUser->hasRole('super_admin') &&
            in_array($maintenanceTask->mt_status_id, ['snz', 'pnd'])
        ) {
            return $authUser->can('MakeWorkOrder:MaintenanceTaskResource');
        }

        if ($authUser->hasRole('manager')) {
            if (
                $authUser->user_dep_id === $maintenanceTask->mt_dep_id &&
                in_array($maintenanceTask->mt_status_id, ['snz', 'pnd'])
            ) {
                return $authUser->can('MakeWorkOrder:MaintenanceTaskResource');
            }
        }

        return false;
    }

    public function snooze(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        if (
            $authUser->hasRole('super_admin') &&
            in_array($maintenanceTask->mt_status_id, ['pnd'])
        ) {
            return $authUser->can('Snooze:MaintenanceTaskResource');
        }

        if ($authUser->hasRole('manager')) {
            if (
                $authUser->user_dep_id === $maintenanceTask->mt_dep_id &&
                in_array($maintenanceTask->mt_status_id, ['pnd'])
            ) {
                return $authUser->can('Snooze:MaintenanceTaskResource');
            }
        }

        return false;
    }

    public function markAsComplete(AuthUser $authUser, MaintenanceTask $maintenanceTask): bool
    {
        if (
            $authUser->hasRole('super_admin') &&
            in_array($maintenanceTask->mt_status_id, ['snz', 'pnd'])
        ) {
            return $authUser->can('MarkAsComplete:MaintenanceTaskResource');
        }

        if ($authUser->hasRole('manager')) {
            if (
                $authUser->user_dep_id === $maintenanceTask->mt_dep_id &&
                in_array($maintenanceTask->mt_status_id, ['snz', 'pnd'])
            ) {
                return $authUser->can('MarkAsComplete:MaintenanceTaskResource');
            }
        }

        return false;
    }
}

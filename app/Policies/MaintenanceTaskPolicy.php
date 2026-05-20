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
        return $authUser->can('View:MaintenanceTaskResource');
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

}
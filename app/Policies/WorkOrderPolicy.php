<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WorkOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkOrderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WorkOrderResource');
    }

    public function view(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        return $authUser->can('View:WorkOrderResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WorkOrderResource');
    }

    public function update(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        return $authUser->can('Update:WorkOrderResource');
    }

    public function delete(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        return $authUser->can('Delete:WorkOrderResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WorkOrderResource');
    }

    public function restore(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        return $authUser->can('Restore:WorkOrderResource');
    }

    public function forceDelete(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        return $authUser->can('ForceDelete:WorkOrderResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WorkOrderResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WorkOrderResource');
    }

    public function replicate(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        return $authUser->can('Replicate:WorkOrderResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WorkOrderResource');
    }

    public function addUpdate(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        return $authUser->can('AddUpdate:WorkOrderResource');
    }

}
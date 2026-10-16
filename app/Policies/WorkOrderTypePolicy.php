<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\WorkOrderType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class WorkOrderTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WorkOrderTypeResource');
    }

    public function view(AuthUser $authUser, WorkOrderType $workOrderType): bool
    {
        return $authUser->can('View:WorkOrderTypeResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WorkOrderTypeResource');
    }

    public function update(AuthUser $authUser, WorkOrderType $workOrderType): bool
    {
        return $authUser->can('Update:WorkOrderTypeResource');
    }

    public function delete(AuthUser $authUser, WorkOrderType $workOrderType): bool
    {
        return $authUser->can('Delete:WorkOrderTypeResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WorkOrderTypeResource');
    }

    public function restore(AuthUser $authUser, WorkOrderType $workOrderType): bool
    {
        return $authUser->can('Restore:WorkOrderTypeResource');
    }

    public function forceDelete(AuthUser $authUser, WorkOrderType $workOrderType): bool
    {
        return $authUser->can('ForceDelete:WorkOrderTypeResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WorkOrderTypeResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WorkOrderTypeResource');
    }

    public function replicate(AuthUser $authUser, WorkOrderType $workOrderType): bool
    {
        return $authUser->can('Replicate:WorkOrderTypeResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WorkOrderTypeResource');
    }
}

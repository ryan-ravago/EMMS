<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RequestorWorkOrder;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RequestorWorkOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RequestorWorkOrderResource');
    }

    public function view(AuthUser $authUser, RequestorWorkOrder $requestorWorkOrder): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return $authUser->can('View:RequestorWorkOrderResource');
        }

        if ($authUser->user_id === $requestorWorkOrder->wo_created_by) {
            return $authUser->can('View:RequestorWorkOrderResource');
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RequestorWorkOrderResource');
    }

    public function update(AuthUser $authUser, RequestorWorkOrder $requestorWorkOrder): bool
    {
        if ($authUser->user_id === $requestorWorkOrder->wo_created_by && $requestorWorkOrder->wo_status_id === 'pnd') {
            return $authUser->can('Update:RequestorWorkOrderResource');
        }

        return false;
    }

    public function delete(AuthUser $authUser, RequestorWorkOrder $requestorWorkOrder): bool
    {
        if ($authUser->user_id === $requestorWorkOrder->wo_created_by && $requestorWorkOrder->wo_status_id === 'pnd') {
            return $authUser->can('Delete:RequestorWorkOrderResource');
        }

        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RequestorWorkOrderResource');
    }

    public function restore(AuthUser $authUser, RequestorWorkOrder $requestorWorkOrder): bool
    {
        return $authUser->can('Restore:RequestorWorkOrderResource');
    }

    public function forceDelete(AuthUser $authUser, RequestorWorkOrder $requestorWorkOrder): bool
    {
        return $authUser->can('ForceDelete:RequestorWorkOrderResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RequestorWorkOrderResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RequestorWorkOrderResource');
    }

    public function replicate(AuthUser $authUser, RequestorWorkOrder $requestorWorkOrder): bool
    {
        return $authUser->can('Replicate:RequestorWorkOrderResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RequestorWorkOrderResource');
    }

    public function cancelWorkOrder(AuthUser $authUser, RequestorWorkOrder $requestorWorkOrder): bool
    {
        return $requestorWorkOrder->wo_status_id === 'pnd' && $authUser->can('ViewAny:RequestorWorkOrderResource');
    }
}

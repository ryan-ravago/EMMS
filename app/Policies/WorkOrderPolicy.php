<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AppUser;
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
        if (
            $authUser->user_dep_id === $workOrder->wo_dep_id
        ) {
            return $authUser->can('View:WorkOrderResource');
        }

        return false;
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
        if (
            $workOrder->wo_status_id === 'inprog' &&
            // $authUser->hasRole('manager') &&
            $authUser->user_dep_id === $workOrder->wo_dep_id
        ) {
            return $authUser->can('AddUpdate:WorkOrderResource');
        }

        return false;
    }

    public function addReport(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if (
            $workOrder->wo_status_id === 'inprog' &&
            // $authUser->hasRole('manager') &&
            $authUser->user_dep_id === $workOrder->wo_dep_id
        ) {
            return $authUser->can('AddReport:WorkOrderResource');
        }

        return false;
    }

    public function approveCompletion(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if (
            $workOrder->wo_status_id === 'pca' &&
            // $authUser->hasRole('manager') &&
            $authUser->user_dep_id === $workOrder->wo_dep_id
        ) {
            return $authUser->can('ApproveCompletion:WorkOrderResource');
        }

        return false;
    }

    public function rejectCompletion(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if (
            $workOrder->wo_status_id === 'pca' &&
            // $authUser->hasRole('manager') &&
            $authUser->user_dep_id === $workOrder->wo_dep_id
        ) {
            return $authUser->can('RejectCompletion:WorkOrderResource');
        }

        return false;
    }

    public function cancelWorkOrder(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if (
            in_array($workOrder->wo_status_id, ['inprog', 'pca']) &&
            $authUser->user_dep_id === $workOrder->wo_dep_id
        ) {
            return $authUser->can('CancelWorkOrder:WorkOrderResource');
        }

        return false;
    }
}

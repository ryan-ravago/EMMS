<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\WorkOrder;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class WorkOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('ViewAny:WorkOrderResource');
    }

    public function view(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_id === $workOrder->wo_created_by && $authUser->hasRole('requestor')) {
            return true;
        }

        if (
            $authUser->user_dep_id === $workOrder->wo_dep_id
            || $authUser->hasRole('execom')
        ) {
            return $authUser->can('View:WorkOrderResource');
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Create:WorkOrderResource');
    }

    public function update(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $this->isSameDepartment($authUser, $workOrder)
            && $authUser->can('Update:WorkOrderResource');
    }

    public function delete(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $this->isSameDepartment($authUser, $workOrder)
            && $authUser->can('Delete:WorkOrderResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('DeleteAny:WorkOrderResource');
    }

    public function restore(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $this->isSameDepartment($authUser, $workOrder)
            && $authUser->can('Restore:WorkOrderResource');
    }

    public function forceDelete(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $this->isSameDepartment($authUser, $workOrder)
            && $authUser->can('ForceDelete:WorkOrderResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('ForceDeleteAny:WorkOrderResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('RestoreAny:WorkOrderResource');
    }

    public function replicate(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $this->isSameDepartment($authUser, $workOrder)
            && $authUser->can('Replicate:WorkOrderResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Reorder:WorkOrderResource');
    }

    public function addUpdate(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($workOrder->wo_status_id !== 'inprog') {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('AddUpdate:WorkOrderResource');
        }

        return false;
    }

    public function addReport(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($workOrder->wo_status_id !== 'inprog') {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('AddReport:WorkOrderResource');
        }

        return false;
    }

    public function approveCompletion(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($workOrder->wo_status_id !== 'pca') {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('ApproveCompletion:WorkOrderResource');
        }

        return false;
    }

    public function rejectCompletion(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($workOrder->wo_status_id !== 'pca') {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('RejectCompletion:WorkOrderResource');
        }

        return false;
    }

    public function cancelWorkOrder(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if (! in_array($workOrder->wo_status_id, ['inprog', 'pca'])) {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('CancelWorkOrder:WorkOrderResource');
        }

        return false;
    }

    public function approveWorkOrder(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($workOrder->wo_status_id !== 'pndwor') {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('ApproveWorkOrder:WorkOrderResource');
        }

        return false;
    }

    public function assignWorkOrder(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($workOrder->wo_status_id !== 'pndwor') {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('AssignWorkOrder:WorkOrderResource');
        }

        return false;
    }

    public function rejectWorkOrder(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($workOrder->wo_status_id !== 'pndwor') {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('RejectWorkOrder:WorkOrderResource');
        }

        return false;
    }

    public function completeWorkOrder(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        if ($workOrder->wo_status_id !== 'inprog') {
            return false;
        }

        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === $workOrder->wo_dep_id) {
            return $authUser->can('CompleteWorkOrder:WorkOrderResource');
        }

        return false;
    }

    /**
     * Work orders belong to a department; only its members may change them.
     */
    private function isSameDepartment(AuthUser $authUser, WorkOrder $workOrder): bool
    {
        return $authUser->user_dep_id !== null
            && (int) $authUser->user_dep_id === (int) $workOrder->wo_dep_id;
    }
}

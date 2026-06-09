<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TechnicianWorkOrder;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TechnicianWorkOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TechnicianWorkOrderResource');
    }

    public function view(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return $authUser->can('View:TechnicianWorkOrderResource');
        }

        if (
            $authUser->user_dep_id === $technicianWorkOrder->wo_dep_id
        ) {
            return $authUser->can('View:TechnicianWorkOrderResource');
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TechnicianWorkOrderResource');
    }

    public function update(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        return $authUser->can('Update:TechnicianWorkOrderResource');
    }

    public function delete(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        return $authUser->can('Delete:TechnicianWorkOrderResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TechnicianWorkOrderResource');
    }

    public function restore(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        return $authUser->can('Restore:TechnicianWorkOrderResource');
    }

    public function forceDelete(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        return $authUser->can('ForceDelete:TechnicianWorkOrderResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TechnicianWorkOrderResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TechnicianWorkOrderResource');
    }

    public function replicate(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        return $authUser->can('Replicate:TechnicianWorkOrderResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TechnicianWorkOrderResource');
    }

    public function addUpdate(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        if ($technicianWorkOrder->wo_status_id === 'inprog') {
            return $authUser->can('AddUpdate:TechnicianWorkOrderResource');
        }

        return false;
    }

    public function addReport(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        if ($technicianWorkOrder->wo_status_id === 'inprog') {
            return $authUser->can('AddReport:TechnicianWorkOrderResource');
        }

        return false;
    }

    public function requestCompletion(AuthUser $authUser, TechnicianWorkOrder $technicianWorkOrder): bool
    {
        if ($technicianWorkOrder->wo_status_id === 'inprog') {
            return $authUser->can('RequestCompletion:TechnicianWorkOrderResource');
        }

        return false;
    }
}

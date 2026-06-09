<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InspectionItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class InspectionItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InspectionItemResource');
    }

    public function view(AuthUser $authUser, InspectionItem $inspectionItem): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return $authUser->can('View:InspectionItemResource');
        }

        $inspection = $inspectionItem->inspection;
        if ($inspection && $authUser->user_dep_id === $inspection->ins_dep_id) {
            return $authUser->can('View:InspectionItemResource');
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InspectionItemResource');
    }

    public function update(AuthUser $authUser, InspectionItem $inspectionItem): bool
    {
        return $authUser->can('Update:InspectionItemResource');
    }

    public function delete(AuthUser $authUser, InspectionItem $inspectionItem): bool
    {
        return $authUser->can('Delete:InspectionItemResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InspectionItemResource');
    }

    public function restore(AuthUser $authUser, InspectionItem $inspectionItem): bool
    {
        return $authUser->can('Restore:InspectionItemResource');
    }

    public function forceDelete(AuthUser $authUser, InspectionItem $inspectionItem): bool
    {
        return $authUser->can('ForceDelete:InspectionItemResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InspectionItemResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InspectionItemResource');
    }

    public function replicate(AuthUser $authUser, InspectionItem $inspectionItem): bool
    {
        return $authUser->can('Replicate:InspectionItemResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InspectionItemResource');
    }

    public function disregard(AuthUser $authUser, InspectionItem $inspectionItem): bool
    {
        if ($authUser->hasRole('super_admin')) {
            if ($inspectionItem->insi_status_id === 'pnd') {
                return $authUser->can('Disregard:InspectionItemResource');
            }
        }

        if ($authUser->hasRole('manager')) {
            if ($inspectionItem->inspection->ins_dep_id === $authUser->user_dep_id && $inspectionItem->insi_status_id === 'pnd') {
                return $authUser->can('Disregard:InspectionItemResource');
            }
        }

        return false;
    }

    public function makeWorkOrder(AuthUser $authUser, InspectionItem $inspectionItem): bool
    {
        if ($authUser->hasRole('super_admin')) {
            if ($inspectionItem->insi_status_id === 'pnd') {
                return $authUser->can('MakeWorkOrder:InspectionItemResource');
            }
        }

        if ($authUser->hasRole('manager')) {
            if (
                $inspectionItem->inspection->ins_dep_id === $authUser->user_dep_id &&
                $inspectionItem->insi_status_id === 'pnd'
            ) {
                return $authUser->can('MakeWorkOrder:InspectionItemResource');
            }
        }

        return false;
    }
}

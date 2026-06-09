<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Inspection;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InspectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InspectionResource');
    }

    public function view(AuthUser $authUser, Inspection $inspection): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return $authUser->can('View:InspectionResource');
        }

        if ($authUser->user_dep_id === $inspection->ins_dep_id) {
            return $authUser->can('View:InspectionResource');
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InspectionResource');
    }

    public function update(AuthUser $authUser, Inspection $inspection): bool
    {
        return $authUser->can('Update:InspectionResource');
    }

    public function delete(AuthUser $authUser, Inspection $inspection): bool
    {
        return $authUser->can('Delete:InspectionResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InspectionResource');
    }

    public function restore(AuthUser $authUser, Inspection $inspection): bool
    {
        return $authUser->can('Restore:InspectionResource');
    }

    public function forceDelete(AuthUser $authUser, Inspection $inspection): bool
    {
        return $authUser->can('ForceDelete:InspectionResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InspectionResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InspectionResource');
    }

    public function replicate(AuthUser $authUser, Inspection $inspection): bool
    {
        return $authUser->can('Replicate:InspectionResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InspectionResource');
    }
}

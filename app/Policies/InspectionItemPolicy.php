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
        return $authUser->can('View:InspectionItemResource');
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

}
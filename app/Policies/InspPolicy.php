<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Insp;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InspPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InspResource');
    }

    public function view(AuthUser $authUser, Insp $insp): bool
    {
        return $authUser->can('View:InspResource')
            && $this->isSameDepartment($authUser, $insp);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InspResource');
    }

    public function update(AuthUser $authUser, Insp $insp): bool
    {
        return $authUser->can('Update:InspResource')
            && $this->isSameDepartment($authUser, $insp);
    }

    public function delete(AuthUser $authUser, Insp $insp): bool
    {
        return $authUser->can('Delete:InspResource')
            && $this->isSameDepartment($authUser, $insp);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InspResource');
    }

    public function restore(AuthUser $authUser, Insp $insp): bool
    {
        return $authUser->can('Restore:InspResource')
            && $this->isSameDepartment($authUser, $insp);
    }

    public function forceDelete(AuthUser $authUser, Insp $insp): bool
    {
        return $authUser->can('ForceDelete:InspResource')
            && $this->isSameDepartment($authUser, $insp);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InspResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InspResource');
    }

    public function replicate(AuthUser $authUser, Insp $insp): bool
    {
        return $authUser->can('Replicate:InspResource')
            && $this->isSameDepartment($authUser, $insp);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InspResource');
    }

    private function isSameDepartment(AuthUser $authUser, Insp $insp): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        if ($authUser->user_dep_id === null) {
            return false;
        }

        return (int) $insp->insp_dep_id === (int) $authUser->user_dep_id;
    }
}

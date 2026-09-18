<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AdminManagerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AdminManagerResource');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:AdminManagerResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AdminManagerResource');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:AdminManagerResource');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:AdminManagerResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AdminManagerResource');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:AdminManagerResource');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:AdminManagerResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AdminManagerResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AdminManagerResource');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:AdminManagerResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AdminManagerResource');
    }
}

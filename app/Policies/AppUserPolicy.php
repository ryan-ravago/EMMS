<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppUserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AppUserResource');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:AppUserResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AppUserResource');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:AppUserResource');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:AppUserResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AppUserResource');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:AppUserResource');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:AppUserResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AppUserResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AppUserResource');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:AppUserResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AppUserResource');
    }

}
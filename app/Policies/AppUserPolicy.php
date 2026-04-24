<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppUserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AppUser');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:AppUser');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AppUser');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:AppUser');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:AppUser');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AppUser');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:AppUser');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:AppUser');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AppUser');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AppUser');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:AppUser');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AppUser');
    }

}
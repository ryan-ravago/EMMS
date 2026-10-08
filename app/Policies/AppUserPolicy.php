<?php

namespace App\Policies;

use App\Models\AppUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

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

    public function update(AuthUser $authUser, ?AppUser $appUser = null): bool
    {
        return $authUser->can('Update:AppUserResource')
            && $this->canManage($authUser, $appUser);
    }

    public function delete(AuthUser $authUser, ?AppUser $appUser = null): bool
    {
        return $authUser->can('Delete:AppUserResource')
            && $this->canManage($authUser, $appUser);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AppUserResource');
    }

    public function restore(AuthUser $authUser, ?AppUser $appUser = null): bool
    {
        return $authUser->can('Restore:AppUserResource')
            && $this->canManage($authUser, $appUser);
    }

    public function forceDelete(AuthUser $authUser, ?AppUser $appUser = null): bool
    {
        return $authUser->can('ForceDelete:AppUserResource')
            && $this->canManage($authUser, $appUser);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AppUserResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AppUserResource');
    }

    public function replicate(AuthUser $authUser, ?AppUser $appUser = null): bool
    {
        return $authUser->can('Replicate:AppUserResource')
            && $this->canManage($authUser, $appUser);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AppUserResource');
    }

    /**
     * Super admin accounts can only be changed by another super admin.
     */
    private function canManage(AuthUser $authUser, ?AppUser $appUser): bool
    {
        return $appUser === null
            || $authUser->hasRole('super_admin')
            || ! $appUser->hasRole('super_admin');
    }
}

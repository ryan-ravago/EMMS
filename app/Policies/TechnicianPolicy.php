<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TechnicianPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TechnicianResource');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:TechnicianResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TechnicianResource');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:TechnicianResource');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:TechnicianResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TechnicianResource');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:TechnicianResource');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:TechnicianResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TechnicianResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TechnicianResource');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:TechnicianResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TechnicianResource');
    }
}

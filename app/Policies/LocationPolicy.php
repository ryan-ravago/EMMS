<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Location;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LocationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('ViewAny:LocationResource');
    }

    public function view(AuthUser $authUser, Location $location): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('View:LocationResource');
    }

    public function create(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Create:LocationResource');
    }

    public function update(AuthUser $authUser, Location $location): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Update:LocationResource');
    }

    public function delete(AuthUser $authUser, Location $location): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Delete:LocationResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('DeleteAny:LocationResource');
    }

    public function restore(AuthUser $authUser, Location $location): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Restore:LocationResource');
    }

    public function forceDelete(AuthUser $authUser, Location $location): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('ForceDelete:LocationResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('ForceDeleteAny:LocationResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('RestoreAny:LocationResource');
    }

    public function replicate(AuthUser $authUser, Location $location): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Replicate:LocationResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Reorder:LocationResource');
    }
}

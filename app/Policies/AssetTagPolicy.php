<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AssetTag;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetTagPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser): ?bool
    {
        return $authUser->hasRole('super_admin') ? true : null;
    }
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssetTagResource');
    }

    public function view(AuthUser $authUser, AssetTag $assetTag): bool
    {
        return $authUser->can('View:AssetTagResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AssetTagResource');
    }

    public function update(AuthUser $authUser, AssetTag $assetTag): bool
    {
        return $authUser->can('Update:AssetTagResource');
    }

    public function delete(AuthUser $authUser, AssetTag $assetTag): bool
    {
        return $authUser->can('Delete:AssetTagResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AssetTagResource');
    }

    public function restore(AuthUser $authUser, AssetTag $assetTag): bool
    {
        return $authUser->can('Restore:AssetTagResource');
    }

    public function forceDelete(AuthUser $authUser, AssetTag $assetTag): bool
    {
        return $authUser->can('ForceDelete:AssetTagResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AssetTagResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AssetTagResource');
    }

    public function replicate(AuthUser $authUser, AssetTag $assetTag): bool
    {
        return $authUser->can('Replicate:AssetTagResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AssetTagResource');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AssetType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AssetTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssetTypeResource');
    }

    public function view(AuthUser $authUser, AssetType $assetType): bool
    {
        return $authUser->can('View:AssetTypeResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AssetTypeResource');
    }

    public function update(AuthUser $authUser, AssetType $assetType): bool
    {
        return $authUser->can('Update:AssetTypeResource');
    }

    public function delete(AuthUser $authUser, AssetType $assetType): bool
    {
        return $authUser->can('Delete:AssetTypeResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AssetTypeResource');
    }

    public function restore(AuthUser $authUser, AssetType $assetType): bool
    {
        return $authUser->can('Restore:AssetTypeResource');
    }

    public function forceDelete(AuthUser $authUser, AssetType $assetType): bool
    {
        return $authUser->can('ForceDelete:AssetTypeResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AssetTypeResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AssetTypeResource');
    }

    public function replicate(AuthUser $authUser, AssetType $assetType): bool
    {
        return $authUser->can('Replicate:AssetTypeResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AssetTypeResource');
    }
}

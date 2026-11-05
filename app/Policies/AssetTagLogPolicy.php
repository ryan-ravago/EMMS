<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AssetTagLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Who may look at the scan logs. They are read-only and permanent: the reader pipeline writes them, so nobody
 * can create, change or delete one from the panel, whatever their permissions.
 */
class AssetTagLogPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser): ?bool
    {
        return $authUser->hasRole('super_admin') ? true : null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssetTagLogResource');
    }

    public function view(AuthUser $authUser, AssetTagLog $assetTagLog): bool
    {
        return $authUser->can('View:AssetTagLogResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, AssetTagLog $assetTagLog): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, AssetTagLog $assetTagLog): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }
}

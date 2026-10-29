<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AssetMovement;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Who may look at and verify equipment trips. The screen is read-only: trips come from tag scans.
 *
 * Manual in is a separate permission so it can be kept to asset admins; the state checks (what an action
 * is allowed on) live on the AssetMovement model.
 */
class AssetMovementPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser): ?bool
    {
        return $authUser->hasRole('super_admin') ? true : null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssetMovementResource');
    }

    public function view(AuthUser $authUser, AssetMovement $assetMovement): bool
    {
        return $authUser->can('View:AssetMovementResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, AssetMovement $assetMovement): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, AssetMovement $assetMovement): bool
    {
        return false;
    }

    public function acknowledge(AuthUser $authUser, AssetMovement $assetMovement): bool
    {
        return $authUser->can('Acknowledge:AssetMovementResource');
    }

    public function manualOut(AuthUser $authUser, AssetMovement $assetMovement): bool
    {
        return $authUser->can('ManualOut:AssetMovementResource');
    }

    public function manualIn(AuthUser $authUser, AssetMovement $assetMovement): bool
    {
        return $authUser->can('ManualIn:AssetMovementResource');
    }

    public function disregard(AuthUser $authUser, AssetMovement $assetMovement): bool
    {
        return $authUser->can('Disregard:AssetMovementResource');
    }

    /**
     * The bulk Acknowledge / Disregard buttons: shown to anyone who may do the action at all. Each selected trip
     * is still checked on its own when the action runs.
     */
    public function acknowledgeAny(AuthUser $authUser): bool
    {
        return $authUser->can('Acknowledge:AssetMovementResource');
    }

    public function disregardAny(AuthUser $authUser): bool
    {
        return $authUser->can('Disregard:AssetMovementResource');
    }

    /**
     * The "Add test scan" button. Not tied to a trip, and granted to no role by default.
     */
    public function addTestLog(AuthUser $authUser): bool
    {
        return $authUser->can('AddTestLog:AssetMovementResource');
    }
}

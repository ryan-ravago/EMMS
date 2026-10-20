<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AimsInspection;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * AIMS inspections are copied from AppSheet and are read-only in EMMS. Only super admins, Asset Admins and Asset
 * Managers can see them; this is decided by role, not by a Shield permission.
 */
class AimsInspectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return AimsInspection::isVisibleTo($authUser);
    }

    public function view(AuthUser $authUser, AimsInspection $aimsInspection): bool
    {
        return AimsInspection::isVisibleTo($authUser);
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, AimsInspection $aimsInspection): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, AimsInspection $aimsInspection): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, AimsInspection $aimsInspection): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, AimsInspection $aimsInspection): bool
    {
        return false;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function replicate(AuthUser $authUser, AimsInspection $aimsInspection): bool
    {
        return false;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return false;
    }
}

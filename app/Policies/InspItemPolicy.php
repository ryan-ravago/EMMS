<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\InspItem;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InspItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InspItemResource');
    }

    public function view(AuthUser $authUser, InspItem $inspItem): bool
    {
        return $authUser->can('View:InspItemResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InspItemResource');
    }

    public function update(AuthUser $authUser, InspItem $inspItem): bool
    {
        return $authUser->can('Update:InspItemResource');
    }

    public function delete(AuthUser $authUser, InspItem $inspItem): bool
    {
        return $authUser->can('Delete:InspItemResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InspItemResource');
    }

    public function restore(AuthUser $authUser, InspItem $inspItem): bool
    {
        return $authUser->can('Restore:InspItemResource');
    }

    public function forceDelete(AuthUser $authUser, InspItem $inspItem): bool
    {
        return $authUser->can('ForceDelete:InspItemResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InspItemResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InspItemResource');
    }

    public function replicate(AuthUser $authUser, InspItem $inspItem): bool
    {
        return $authUser->can('Replicate:InspItemResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InspItemResource');
    }
}

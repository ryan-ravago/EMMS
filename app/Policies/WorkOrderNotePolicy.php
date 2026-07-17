<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WorkOrderNote;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkOrderNotePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WorkOrderNoteResource');
    }

    public function view(AuthUser $authUser, WorkOrderNote $workOrderNote): bool
    {
        return $authUser->can('View:WorkOrderNoteResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WorkOrderNoteResource');
    }

    public function update(AuthUser $authUser, WorkOrderNote $workOrderNote): bool
    {
        return $authUser->can('Update:WorkOrderNoteResource');
    }

    public function delete(AuthUser $authUser, WorkOrderNote $workOrderNote): bool
    {
        return $authUser->can('Delete:WorkOrderNoteResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WorkOrderNoteResource');
    }

    public function restore(AuthUser $authUser, WorkOrderNote $workOrderNote): bool
    {
        return $authUser->can('Restore:WorkOrderNoteResource');
    }

    public function forceDelete(AuthUser $authUser, WorkOrderNote $workOrderNote): bool
    {
        return $authUser->can('ForceDelete:WorkOrderNoteResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WorkOrderNoteResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WorkOrderNoteResource');
    }

    public function replicate(AuthUser $authUser, WorkOrderNote $workOrderNote): bool
    {
        return $authUser->can('Replicate:WorkOrderNoteResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WorkOrderNoteResource');
    }
}

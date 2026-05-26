<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Task;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TaskResource');
    }

    public function view(AuthUser $authUser, Task $task): bool
    {
        return $authUser->can('View:TaskResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TaskResource');
    }

    public function update(AuthUser $authUser, Task $task): bool
    {
        return $authUser->can('Update:TaskResource');
    }

    public function delete(AuthUser $authUser, Task $task): bool
    {
        return $authUser->can('Delete:TaskResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TaskResource');
    }

    public function restore(AuthUser $authUser, Task $task): bool
    {
        return $authUser->can('Restore:TaskResource');
    }

    public function forceDelete(AuthUser $authUser, Task $task): bool
    {
        return $authUser->can('ForceDelete:TaskResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TaskResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TaskResource');
    }

    public function replicate(AuthUser $authUser, Task $task): bool
    {
        return $authUser->can('Replicate:TaskResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TaskResource');
    }

}
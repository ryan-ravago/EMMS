<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Department;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DepartmentResource');
    }

    public function view(AuthUser $authUser, Department $department): bool
    {
        return $authUser->can('View:DepartmentResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DepartmentResource');
    }

    public function update(AuthUser $authUser, Department $department): bool
    {
        return $authUser->can('Update:DepartmentResource');
    }

    public function delete(AuthUser $authUser, Department $department): bool
    {
        return $authUser->can('Delete:DepartmentResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DepartmentResource');
    }

    public function restore(AuthUser $authUser, Department $department): bool
    {
        return $authUser->can('Restore:DepartmentResource');
    }

    public function forceDelete(AuthUser $authUser, Department $department): bool
    {
        return $authUser->can('ForceDelete:DepartmentResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DepartmentResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DepartmentResource');
    }

    public function replicate(AuthUser $authUser, Department $department): bool
    {
        return $authUser->can('Replicate:DepartmentResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DepartmentResource');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EquipmentCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CategoryResource');
    }

    public function view(AuthUser $authUser, EquipmentCategory $equipmentCategory): bool
    {
        return $authUser->can('View:CategoryResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CategoryResource');
    }

    public function update(AuthUser $authUser, EquipmentCategory $equipmentCategory): bool
    {
        return $authUser->can('Update:CategoryResource');
    }

    public function delete(AuthUser $authUser, EquipmentCategory $equipmentCategory): bool
    {
        return $authUser->can('Delete:CategoryResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CategoryResource');
    }

    public function restore(AuthUser $authUser, EquipmentCategory $equipmentCategory): bool
    {
        return $authUser->can('Restore:CategoryResource');
    }

    public function forceDelete(AuthUser $authUser, EquipmentCategory $equipmentCategory): bool
    {
        return $authUser->can('ForceDelete:CategoryResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CategoryResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CategoryResource');
    }

    public function replicate(AuthUser $authUser, EquipmentCategory $equipmentCategory): bool
    {
        return $authUser->can('Replicate:CategoryResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CategoryResource');
    }

}
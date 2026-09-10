<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EquipmentUnitCategory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EquipmentUnitCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EquipmentUnitCategoryResource');
    }

    public function view(AuthUser $authUser, EquipmentUnitCategory $equipmentUnitCategory): bool
    {
        return $authUser->can('View:EquipmentUnitCategoryResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EquipmentUnitCategoryResource');
    }

    public function update(AuthUser $authUser, EquipmentUnitCategory $equipmentUnitCategory): bool
    {
        return $authUser->can('Update:EquipmentUnitCategoryResource');
    }

    public function delete(AuthUser $authUser, EquipmentUnitCategory $equipmentUnitCategory): bool
    {
        return $authUser->can('Delete:EquipmentUnitCategoryResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EquipmentUnitCategoryResource');
    }

    public function restore(AuthUser $authUser, EquipmentUnitCategory $equipmentUnitCategory): bool
    {
        return $authUser->can('Restore:EquipmentUnitCategoryResource');
    }

    public function forceDelete(AuthUser $authUser, EquipmentUnitCategory $equipmentUnitCategory): bool
    {
        return $authUser->can('ForceDelete:EquipmentUnitCategoryResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EquipmentUnitCategoryResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EquipmentUnitCategoryResource');
    }

    public function replicate(AuthUser $authUser, EquipmentUnitCategory $equipmentUnitCategory): bool
    {
        return $authUser->can('Replicate:EquipmentUnitCategoryResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EquipmentUnitCategoryResource');
    }

    public function allocateCategory(AuthUser $authUser): bool
    {
        return $authUser->can('AllocateCategory:EquipmentUnitCategoryResource');
    }
}

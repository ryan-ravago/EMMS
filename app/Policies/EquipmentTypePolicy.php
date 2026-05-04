<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EquipmentType;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EquipmentTypeResource');
    }

    public function view(AuthUser $authUser, EquipmentType $equipmentType): bool
    {
        return $authUser->can('View:EquipmentTypeResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EquipmentTypeResource');
    }

    public function update(AuthUser $authUser, EquipmentType $equipmentType): bool
    {
        return $authUser->can('Update:EquipmentTypeResource');
    }

    public function delete(AuthUser $authUser, EquipmentType $equipmentType): bool
    {
        return $authUser->can('Delete:EquipmentTypeResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EquipmentTypeResource');
    }

    public function restore(AuthUser $authUser, EquipmentType $equipmentType): bool
    {
        return $authUser->can('Restore:EquipmentTypeResource');
    }

    public function forceDelete(AuthUser $authUser, EquipmentType $equipmentType): bool
    {
        return $authUser->can('ForceDelete:EquipmentTypeResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EquipmentTypeResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EquipmentTypeResource');
    }

    public function replicate(AuthUser $authUser, EquipmentType $equipmentType): bool
    {
        return $authUser->can('Replicate:EquipmentTypeResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EquipmentTypeResource');
    }

}
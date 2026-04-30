<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Equipment;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EquipmentResource');
    }

    public function view(AuthUser $authUser, Equipment $equipment): bool
    {
        return $authUser->can('View:EquipmentResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EquipmentResource');
    }

    public function update(AuthUser $authUser, Equipment $equipment): bool
    {
        return $authUser->can('Update:EquipmentResource');
    }

    public function delete(AuthUser $authUser, Equipment $equipment): bool
    {
        return $authUser->can('Delete:EquipmentResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EquipmentResource');
    }

    public function restore(AuthUser $authUser, Equipment $equipment): bool
    {
        return $authUser->can('Restore:EquipmentResource');
    }

    public function forceDelete(AuthUser $authUser, Equipment $equipment): bool
    {
        return $authUser->can('ForceDelete:EquipmentResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EquipmentResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EquipmentResource');
    }

    public function replicate(AuthUser $authUser, Equipment $equipment): bool
    {
        return $authUser->can('Replicate:EquipmentResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EquipmentResource');
    }

}
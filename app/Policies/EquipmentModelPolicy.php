<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EquipmentModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentModelPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ModelResource');
    }

    public function view(AuthUser $authUser, EquipmentModel $equipmentModel): bool
    {
        return $authUser->can('View:ModelResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ModelResource');
    }

    public function update(AuthUser $authUser, EquipmentModel $equipmentModel): bool
    {
        return $authUser->can('Update:ModelResource');
    }

    public function delete(AuthUser $authUser, EquipmentModel $equipmentModel): bool
    {
        return $authUser->can('Delete:ModelResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ModelResource');
    }

    public function restore(AuthUser $authUser, EquipmentModel $equipmentModel): bool
    {
        return $authUser->can('Restore:ModelResource');
    }

    public function forceDelete(AuthUser $authUser, EquipmentModel $equipmentModel): bool
    {
        return $authUser->can('ForceDelete:ModelResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ModelResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ModelResource');
    }

    public function replicate(AuthUser $authUser, EquipmentModel $equipmentModel): bool
    {
        return $authUser->can('Replicate:ModelResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ModelResource');
    }

}
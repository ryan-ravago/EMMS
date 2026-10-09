<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Equipment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EquipmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('ViewAny:EquipmentResource');
    }

    public function view(AuthUser $authUser, Equipment $equipment): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('View:EquipmentResource');
    }

    public function create(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Create:EquipmentResource');
    }

    public function update(AuthUser $authUser, Equipment $equipment): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Update:EquipmentResource');
    }

    public function delete(AuthUser $authUser, Equipment $equipment): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Delete:EquipmentResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('DeleteAny:EquipmentResource');
    }

    public function restore(AuthUser $authUser, Equipment $equipment): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Restore:EquipmentResource');
    }

    public function forceDelete(AuthUser $authUser, Equipment $equipment): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('ForceDelete:EquipmentResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('ForceDeleteAny:EquipmentResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('RestoreAny:EquipmentResource');
    }

    public function replicate(AuthUser $authUser, Equipment $equipment): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Replicate:EquipmentResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Reorder:EquipmentResource');
    }

    public function sync(AuthUser $authUser, Equipment $equipment): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Sync:EquipmentResource');
    }

    public function export(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('Export:EquipmentResource');
    }

    public function previewExport(AuthUser $authUser): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return $authUser->can('PreviewExport:EquipmentResource');
    }

    public function allocate(AuthUser $authUser, Equipment $equipment): bool
    {
        return $equipment->asset_type_id === 2
            && $equipment->lifecycle_status_id !== 'alc'
            && $authUser->can('Allocate:EquipmentResource');
    }

    public function deploy(AuthUser $authUser, Equipment $equipment): bool
    {
        return $equipment->asset_type_id === 1
            && $equipment->lifecycle_status_id !== 'dep'
            && $authUser->can('Deploy:EquipmentResource');
    }

    public function setToIdle(AuthUser $authUser, Equipment $equipment): bool
    {
        return $equipment->lifecycle_status_id !== 'idle'
            && $authUser->can('SetToIdle:EquipmentResource');
    }

    public function setToMaintenance(AuthUser $authUser, Equipment $equipment): bool
    {
        return $equipment->lifecycle_status_id !== 'udmt'
            && $authUser->can('SetToMaintenance:EquipmentResource');
    }
}

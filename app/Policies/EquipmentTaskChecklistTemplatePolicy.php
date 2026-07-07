<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EquipmentTaskChecklistTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentTaskChecklistTemplatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EquipmentTaskChecklistTemplateResource');
    }

    public function view(AuthUser $authUser, EquipmentTaskChecklistTemplate $equipmentTaskChecklistTemplate): bool
    {
        return $authUser->can('View:EquipmentTaskChecklistTemplateResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EquipmentTaskChecklistTemplateResource');
    }

    public function update(AuthUser $authUser, EquipmentTaskChecklistTemplate $equipmentTaskChecklistTemplate): bool
    {
        return $authUser->can('Update:EquipmentTaskChecklistTemplateResource');
    }

    public function delete(AuthUser $authUser, EquipmentTaskChecklistTemplate $equipmentTaskChecklistTemplate): bool
    {
        return $authUser->can('Delete:EquipmentTaskChecklistTemplateResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EquipmentTaskChecklistTemplateResource');
    }

    public function restore(AuthUser $authUser, EquipmentTaskChecklistTemplate $equipmentTaskChecklistTemplate): bool
    {
        return $authUser->can('Restore:EquipmentTaskChecklistTemplateResource');
    }

    public function forceDelete(AuthUser $authUser, EquipmentTaskChecklistTemplate $equipmentTaskChecklistTemplate): bool
    {
        return $authUser->can('ForceDelete:EquipmentTaskChecklistTemplateResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EquipmentTaskChecklistTemplateResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EquipmentTaskChecklistTemplateResource');
    }

    public function replicate(AuthUser $authUser, EquipmentTaskChecklistTemplate $equipmentTaskChecklistTemplate): bool
    {
        return $authUser->can('Replicate:EquipmentTaskChecklistTemplateResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EquipmentTaskChecklistTemplateResource');
    }

}
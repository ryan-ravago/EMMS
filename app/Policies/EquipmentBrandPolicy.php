<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EquipmentBrand;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentBrandPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BrandResource');
    }

    public function view(AuthUser $authUser, EquipmentBrand $equipmentBrand): bool
    {
        return $authUser->can('View:BrandResource');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BrandResource');
    }

    public function update(AuthUser $authUser, EquipmentBrand $equipmentBrand): bool
    {
        return $authUser->can('Update:BrandResource');
    }

    public function delete(AuthUser $authUser, EquipmentBrand $equipmentBrand): bool
    {
        return $authUser->can('Delete:BrandResource');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BrandResource');
    }

    public function restore(AuthUser $authUser, EquipmentBrand $equipmentBrand): bool
    {
        return $authUser->can('Restore:BrandResource');
    }

    public function forceDelete(AuthUser $authUser, EquipmentBrand $equipmentBrand): bool
    {
        return $authUser->can('ForceDelete:BrandResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BrandResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BrandResource');
    }

    public function replicate(AuthUser $authUser, EquipmentBrand $equipmentBrand): bool
    {
        return $authUser->can('Replicate:BrandResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BrandResource');
    }

}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $table = 'statuses';

    protected $primaryKey = 'status_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'status_title',
        'status_color',
        'status_icon',
    ];

    public $timestamps = false;

    public function equipmentUnits(): HasMany
    {
        return $this->hasMany(Equipment::class, 'lifecycle_status_id', 'status_id');
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class, 'mt_status_id', 'status_id');
    }

    public function inspectionItems(): HasMany
    {
        return $this->hasMany(InspectionItem::class, 'insi_status_id', 'status_id');
    }
}

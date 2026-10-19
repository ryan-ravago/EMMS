<?php

namespace App\Models;

use Database\Factories\MaintenanceInspectionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceInspectionItem extends Model
{
    /** @use HasFactory<MaintenanceInspectionItemFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'maintenance_inspection_id',
        'task',
        'result',
        'remarks',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(MaintenanceInspection::class, 'maintenance_inspection_id');
    }
}

<?php

namespace App\Models;

use Database\Factories\MaintenanceInspectionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceInspectionLog extends Model
{
    /** @use HasFactory<MaintenanceInspectionLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'maintenance_inspection_id',
        'action',
        'status',
        'remarks',
        'performed_by',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(MaintenanceInspection::class, 'maintenance_inspection_id');
    }
}

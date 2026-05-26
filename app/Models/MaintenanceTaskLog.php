<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceTaskLog extends Model
{
    protected $table = 'maintenance_task_logs';
    protected $primaryKey = 'mtl_id';

    protected $fillable = [
        'mtl_mt_id',
        'mtl_status_id',
        'mtl_due_dt',
        'mtl_last_act_made',
        'mtl_remarks',
        'mtl_by',
        'mtl_dt',
    ];

    public $timestamps = false;

    public function maintenanceTask(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTask::class, 'mtl_mt_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'mtl_status_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class, 'mtl_last_act_made');
    }

    public function logBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'mtl_by');
    }
}

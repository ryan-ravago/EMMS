<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class WorkOrder extends Model
{
    protected $table = 'work_orders';
    protected $primaryKey = 'wo_id';
    public $timestamps = false;

    protected $fillable = [
        'wo_no',
        'wo_eqm_id',
        'wo_dep_id',
        'wo_mt_id',
        'wo_insi_id',
        'wo_title',
        'wo_desc',
        'wo_prio_id',
        'wo_status_id',
        'wo_attachments',
        'wo_created_by',
        'wo_created_dt',
        'wo_closed_dt',
    ];

    protected $casts = [
        'wo_attachments' => 'array',
    ];

    public static $bootedNow;

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'wo_eqm_id', 'eqm_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'wo_dep_id', 'dep_id');
    }

    public function maintenanceTask(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTask::class, 'wo_mt_id', 'mt_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'wo_prio_id', 'prio_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'wo_status_id', 'status_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'wo_created_by', 'user_id');
    }

    public function workers(): BelongsToMany
    {
        return $this->belongsToMany(AppUser::class, 'work_order_assignments', 'woa_wo_id', 'woa_worker_id', 'wo_id', 'user_id');
    }

    public function logUpdates(): HasMany
    {
        return $this->hasMany(WorkOrderLogUpdate::class, 'wolu_wo_id', 'wo_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkOrderLog::class, 'wol_wo_id', 'wo_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderLog extends Model
{
    protected $table = 'work_order_logs';
    protected $primaryKey = 'wol_id';
    public $timestamps = false;

    protected $fillable = [
        'wol_wo_id',
        'wol_a_id',
        'wol_status_id',
        'wol_a_log',
        'wol_status_log',
        'wol_note',
        'wol_by',
        'wol_dt',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'wol_wo_id', 'wo_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class, 'wol_a_id', 'a_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'wol_status_id', 'status_id');
    }

    public function by(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'wol_by', 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderAssignment extends Model
{
    // protected $table = 'work_order_assignments';
    // protected $primaryKey = 'woa_id';
    // public $timestamps = false;

    // protected $fillable = [
    //     'woa_wo_id',
    //     'woa_worker_id',
    // ];

    // public function workOrder(): BelongsTo
    // {
    //     return $this->belongsTo(WorkOrder::class, 'woa_wo_id', 'wo_id');
    // }

    // public function worker(): BelongsTo
    // {
    //     return $this->belongsTo(AppUser::class, 'woa_worker_id', 'user_id');
    // }
}

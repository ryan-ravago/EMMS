<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderLogUpdate extends Model
{
    protected $table = 'work_order_log_updates';
    protected $primaryKey = 'wolu_id';
    public $timestamps = false;

    protected $fillable = [
        'wolu_wo_id',
        'wolu_update_note',
        'wolu_attachments',
        'wolu_by',
        'wolu_dt',
    ];

    protected $casts = [
        'wolu_attachments' => 'array',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'wolu_wo_id', 'wo_id');
    }

    public function by(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'wolu_by', 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionItemLog extends Model
{
    protected $table = 'inspection_item_logs';

    protected $primaryKey = 'inil_id';

    public $timestamps = false;

    protected $fillable = [
        'inil_insi_id',
        'inil_a_id',
        'inil_status_id',
        'inil_action_made',
        'inil_status_log',
        'inil_remarks',
        'inil_wo_id',
        'inil_by',
        'inil_dt',
    ];

    protected $casts = [
        'inil_dt' => 'datetime',
    ];

    public function inspectionItem(): BelongsTo
    {
        return $this->belongsTo(InspectionItem::class, 'inil_insi_id', 'insi_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'inil_by', 'user_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'inil_wo_id', 'wo_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'inil_status_id', 'status_id');
    }
}

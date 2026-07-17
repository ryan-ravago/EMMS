<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderNote extends Model
{
    protected $table = 'work_order_notes';

    protected $primaryKey = 'won_id';

    public $timestamps = false;

    protected $fillable = [
        'won_wo_id',
        'won_note',
        'won_attachments',
        'won_created_by',
        'won_created_at',
    ];

    protected function casts(): array
    {
        return [
            'won_attachments' => 'array',
            'won_created_at' => 'datetime',
        ];
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'won_wo_id');
    }

    public function creator()
    {
        return $this->belongsTo(AppUser::class, 'won_created_by');
    }
}

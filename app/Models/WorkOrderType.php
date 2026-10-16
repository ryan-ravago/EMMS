<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Database\Factories\WorkOrderTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrderType extends Model
{
    /** @use HasFactory<WorkOrderTypeFactory> */
    use HasFactory;

    use LogsChanges;

    public $timestamps = false;

    protected $fillable = [
        'code',
        'type',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'wo_type_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    protected $table = 'priorities';
    protected $primaryKey = 'prio_id';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'prio_name',
    ];

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'wo_prio_id', 'prio_id');
    }
}

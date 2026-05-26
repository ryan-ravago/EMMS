<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TechnicianWorkOrder extends WorkOrder
{
    protected static function booted(): void
    {
        static::addGlobalScope('technician', function (Builder $query) {
            $query->whereHas('workers', fn($q) => $q->where('user_id', auth()->id()));
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncAuditLog extends Model
{
    protected $fillable = [
        'sheet_name',
        'synced_rows',
        'last_row_processed',
        'status',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use App\Services\Rfid\RfidHealthReport;
use Illuminate\Database\Eloquent\Model;

/**
 * The single row that remembers where the RFID reader alert stands (see rfid:check-health).
 */
class RfidHealthState extends Model
{
    protected $fillable = [
        'status',
        'cause',
        'since',
        'last_notified_at',
        'token_warning_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'since' => 'datetime',
            'last_notified_at' => 'datetime',
            'token_warning_sent_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], ['status' => RfidHealthReport::OK]);
    }
}

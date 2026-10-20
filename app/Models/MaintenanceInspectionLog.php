<?php

namespace App\Models;

use Database\Factories\MaintenanceInspectionLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceInspectionLog extends Model
{
    /** @use HasFactory<MaintenanceInspectionLogFactory> */
    use HasFactory;

    use MassPrunable;

    /**
     * Log rows older than the retention period, by when they were logged (logged_at). Rows without a date
     * are kept, because their age cannot be known.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::query()->where('logged_at', '<', now()->subDays((int) config('log_retention.days')));
    }

    public $timestamps = false;

    protected $fillable = [
        'maintenance_inspection_id',
        'action',
        'status',
        'remarks',
        'performed_by',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(MaintenanceInspection::class, 'maintenance_inspection_id');
    }
}

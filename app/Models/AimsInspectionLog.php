<?php

namespace App\Models;

use Database\Factories\AimsInspectionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AimsInspectionLog extends Model
{
    /** @use HasFactory<AimsInspectionLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'aims_inspection_id',
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
        return $this->belongsTo(AimsInspection::class, 'aims_inspection_id');
    }
}

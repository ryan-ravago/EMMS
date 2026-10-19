<?php

namespace App\Models;

use Database\Factories\AimsInspectionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AimsInspectionItem extends Model
{
    /** @use HasFactory<AimsInspectionItemFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'aims_inspection_id',
        'task',
        'result',
        'remarks',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(AimsInspection::class, 'aims_inspection_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspItem extends Model
{
    protected $table = 'insp_items';

    protected $primaryKey = 'inspi_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'inspi_id',
        'inspi_insp_id',
        'inspi_task',
        'inspi_result',
        'inspi_remarks',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Insp::class, 'inspi_insp_id', 'insp_id');
    }
}

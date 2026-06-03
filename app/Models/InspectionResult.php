<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionResult extends Model
{
    protected $table = 'inspection_results';
    protected $primaryKey = 'insr_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'insr_code',
        'insr_name',
        'insr_color',
    ];

    public function inspectionItems(): HasMany
    {
        return $this->hasMany(InspectionItem::class, 'insi_result', 'insr_code');
    }
}

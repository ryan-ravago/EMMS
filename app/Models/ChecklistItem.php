<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    protected $table = 'checklist_items';
    protected $primaryKey = 'cli_id';

    protected $fillable = [
        'cli_clt_id',
        'cli_name',
        'cli_sort_order',
        'cli_created_by',
        'cli_created_at',
        'cli_last_updated_by',
        'cli_last_updated_at',
    ];

    public $timestamps = false;

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'cli_clt_id', 'clt_id');
    }
}

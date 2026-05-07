<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistUsageType extends Model
{
    protected $table = 'checklist_usage_types';
    protected $primaryKey = 'cut_id';

    protected $fillable = ['cut_name'];

    public $timestamps = false;

    public function checklistTemplates(): HasMany
    {
        return $this->hasMany(ChecklistTemplate::class, 'clt_cut_id', 'cut_id');
    }
}

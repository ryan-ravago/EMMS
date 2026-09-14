<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AssetEditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'asset_id',
        'changes',
        'performed_by',
        'logged_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * @var array<string, string>
     */
    protected static array $fieldLabels = [
        'asset_type_id' => 'Asset Type',
        'parent_id' => 'Parent Equipment',
        'lifecycle_status_id' => 'Lifecycle Status',
        'location_id' => 'Location',
        'year_model' => 'Year Model',
        'specifications' => 'Specifications',
        'eqmc_name' => 'Name',
        'eqm_eqmm_id' => 'Model',
        'eqm_brand_id' => 'Brand',
        'eqm_eqmt_id' => 'Type',
        'eqm_chassis_no' => 'Chassis No.',
        'eqm_date_purchased' => 'Date Purchased',
        'eqm_name' => 'Name',
        'eqm_pm_itrv_type' => 'PM Interval Type',
        'eqm_pm_itrv_value' => 'PM Interval Value',
        'eqm_pm_itrv_start_date' => 'PM Interval Start Date',
        'eqm_next_pm_due_at' => 'Next PM Due',
        'eqm_last_pm_notified_at' => 'Last PM Notified',
        'eqm_vin' => 'VIN',
        'eqm_plate_num' => 'Plate No.',
        'eqm_prc_code' => 'PRC Code',
        'eqm_serial_num' => 'Serial No.',
        'eqm_engine' => 'Engine',
        'eqm_is_active' => 'Active',
        'categories' => 'Tags',
    ];

    /**
     * @var array<string, array{class: class-string<Model>, column: string}>
     */
    protected static array $relatedLookups = [
        'asset_type_id' => ['class' => AssetType::class, 'column' => 'name'],
        'location_id' => ['class' => Location::class, 'column' => 'name'],
        'lifecycle_status_id' => ['class' => Status::class, 'column' => 'status_title'],
        'parent_id' => ['class' => Equipment::class, 'column' => 'eqm_name'],
        'eqm_eqmm_id' => ['class' => EquipmentModel::class, 'column' => 'eqmm_name'],
        'eqm_brand_id' => ['class' => EquipmentBrand::class, 'column' => 'eqmb_name'],
        'eqm_eqmt_id' => ['class' => EquipmentType::class, 'column' => 'eqmt_name'],
    ];

    public static function fieldLabel(string $field): string
    {
        return static::$fieldLabels[$field] ?? Str::of($field)->replace('_', ' ')->title()->toString();
    }

    public static function resolveFieldValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '' || $value === []) {
            return '-';
        }

        if (is_array($value)) {
            return collect($value)->map(fn ($item) => (string) $item)->implode(', ');
        }

        if (isset(static::$relatedLookups[$field]) && (is_int($value) || is_string($value))) {
            $lookup = static::$relatedLookups[$field];

            /** @var Model|null $related */
            $related = $lookup['class']::query()->find($value);

            return $related?->{$lookup['column']} ?? "#{$value}";
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'asset_id', 'eqm_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'performed_by', 'user_id');
    }
}

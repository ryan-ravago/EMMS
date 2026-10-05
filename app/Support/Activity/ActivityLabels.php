<?php

namespace App\Support\Activity;

use App\Models\Action as ActionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/**
 * Turns raw activity_log rows into plain language: what happened, to which record, and what changed.
 */
class ActivityLabels
{
    /** Names users know for models whose class name reads differently. */
    private const TYPE_NAMES = [
        'AppUser' => 'User',
        'AdminManager' => 'User',
        'Technician' => 'User',
        'EquipmentBrand' => 'Brand',
        'EquipmentCategory' => 'Category',
        'EquipmentModel' => 'Equipment Model',
        'RequestorWorkOrder' => 'Work Order',
        'TechnicianWorkOrder' => 'Work Order',
        'Insp' => 'Inspection',
    ];

    private const MODEL_EVENTS = [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'restored' => 'Restored',
    ];

    private const FIELD_NAMES = [
        'fname' => 'First Name',
        'mname' => 'Middle Name',
        'lname' => 'Last Name',
    ];

    /** Leading segments that are real words, not a table prefix like "eqm_" or "wo_". */
    private const KEEP_PREFIX = ['is', 'has', 'last', 'next', 'can'];

    /**
     * Foreign keys shown by name instead of number (Status: Pending → Approved, not 2 → 3).
     * Column => model class.
     */
    private const LOOKUPS = [
        'wo_status_id' => \App\Models\Status::class,
        'wo_prio_id' => \App\Models\Priority::class,
        'wo_dep_id' => \App\Models\Department::class,
        'user_dep_id' => \App\Models\Department::class,
        'insp_dep_id' => \App\Models\Department::class,
        'wo_eqm_id' => \App\Models\Equipment::class,
        'insp_eqm_id' => \App\Models\Equipment::class,
        'asset_parent_id' => \App\Models\Equipment::class,
        'wo_created_by' => \App\Models\AppUser::class,
        'won_created_by' => \App\Models\AppUser::class,
        'won_wo_id' => \App\Models\WorkOrder::class,
        'asset_type_id' => \App\Models\AssetType::class,
        'location_id' => \App\Models\Location::class,
        'eqm_eqmm_id' => \App\Models\EquipmentModel::class,
        'eqm_brand_id' => \App\Models\EquipmentBrand::class,
        'eqmm_brand_id' => \App\Models\EquipmentBrand::class,
        'eqm_eqmt_id' => \App\Models\EquipmentType::class,
        'eqmm_eqmt_id' => \App\Models\EquipmentType::class,
        'eqmc_parent_id' => \App\Models\EquipmentCategory::class,
    ];

    /** @var array<string, ActionModel|null> */
    private static array $actions = [];

    /** @var array<string, string> */
    private static array $lookups = [];

    public static function typeName(?string $class): string
    {
        $base = class_basename((string) $class);

        return self::TYPE_NAMES[$base] ?? (Str::headline($base) ?: 'Record');
    }

    public static function subjectLabel(Model $model): string
    {
        if (method_exists($model, 'activityLabel')) {
            return (string) $model->activityLabel();
        }

        foreach (['name', 'title', 'no', 'code'] as $suffix) {
            foreach ($model->getAttributes() as $key => $value) {
                if (is_scalar($value) && $value !== '' && preg_match('/(^|_)'.$suffix.'$/', (string) $key)) {
                    return Str::limit((string) $value, 60);
                }
            }
        }

        return '#'.$model->getKey();
    }

    /** Past-tense wording for the "Action" column: Created, Updated, Deleted, Approved, ... */
    public static function eventLabel(?string $event): string
    {
        if ($event === null || $event === '') {
            return 'Logged';
        }

        if (isset(self::MODEL_EVENTS[$event])) {
            return self::MODEL_EVENTS[$event];
        }

        if ($event === 'scheduled') {
            return 'Scheduled task';
        }

        return self::action($event)?->a_past_tense ?? Str::headline($event);
    }

    /**
     * Field-level changes as readable rows. Understands both the Spatie shape
     * (attributes / old) and hand-written diffs ('field' => ['old' => .., 'new' => ..]).
     *
     * @return array<int, array{field: string, old: string, new: string}>
     */
    public static function changes(Activity $activity): array
    {
        $properties = self::properties($activity);
        $new = $properties->get('attributes');
        $rows = [];

        if (is_array($new)) {
            $old = $properties->get('old');
            $old = is_array($old) ? $old : [];

            foreach ($new as $key => $value) {
                $rows[] = [
                    'field' => self::fieldLabel((string) $key),
                    'old' => array_key_exists($key, $old) ? self::formatValue($old[$key], (string) $key) : '—',
                    'new' => self::formatValue($value, (string) $key),
                ];
            }

            return $rows;
        }

        foreach ($properties as $key => $value) {
            if (is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value)) {
                $rows[] = [
                    'field' => self::fieldLabel((string) $key),
                    'old' => self::formatValue($value['old'], (string) $key),
                    'new' => self::formatValue($value['new'], (string) $key),
                ];
            }
        }

        return $rows;
    }

    /** One-line summary of the first few changes, e.g. "Name: A → B; Active: Yes → No; +2 more". */
    public static function summarize(array $changes, int $limit = 3): string
    {
        $parts = array_map(
            fn (array $change): string => $change['field'].': '.Str::limit($change['old'], 30).' → '.Str::limit($change['new'], 30),
            array_slice($changes, 0, $limit),
        );

        $more = count($changes) - $limit;

        return implode('; ', $parts).($more > 0 ? "; +{$more} more" : '');
    }

    /** Builds the sentence stored in activity_log.description. */
    public static function describe(Activity $activity, ?Model $subject): string
    {
        $original = (string) $activity->description;
        $event = $activity->event;

        if ($event === 'scheduled' || ($subject === null && ! $activity->subject_type)) {
            return $original;
        }

        $properties = self::properties($activity);
        $type = self::typeName($subject ? $subject::class : $activity->subject_type);
        $label = $properties->get('subject_label');
        $target = $label ? "{$type} \"{$label}\"" : $type;

        $isModelEvent = isset(self::MODEL_EVENTS[$event]) || ($event && self::action($event));

        if ($isModelEvent) {
            $text = self::eventLabel($event).' '.$target;

            if ($event === 'updated' && ($changes = self::changes($activity))) {
                $text .= ' ('.self::summarize($changes).')';
            }
        } else {
            // Hand-written entries ("Marked as Idle"): keep the wording, add the record.
            $text = ($original !== '' && $label && str_contains($original, (string) $label))
                ? $original
                : trim($original.' — '.$target, ' —');
        }

        if ($via = $properties->get('via')) {
            $text .= " [{$via}]";
        }

        return $text;
    }

    public static function userName(Activity $activity): string
    {
        if ($activity->causer) {
            return trim("{$activity->causer->user_fname} {$activity->causer->user_lname}");
        }

        return $activity->event === 'scheduled' ? 'Scheduled task' : 'System';
    }

    public static function recordName(Activity $activity): ?string
    {
        if (! $activity->subject_type) {
            return null;
        }

        return $activity->getExtraProperty('subject_label') ?: '#'.$activity->subject_id;
    }

    /** Everything the activity details modal shows, prepared here so the view stays plain markup. */
    public static function details(Activity $activity): array
    {
        $properties = self::properties($activity);

        // Whatever is left after the parts that have their own section.
        $extra = $properties
            ->except(['attributes', 'old', 'subject_label', 'via', 'bulk', 'action_id'])
            ->reject(fn ($value) => is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value))
            ->mapWithKeys(fn ($value, $key): array => [Str::headline((string) $key) => self::formatValue($value, (string) $key)])
            ->all();

        $batch = $activity->batch_uuid
            ? Activity::query()->where('batch_uuid', $activity->batch_uuid)
            : null;

        return [
            'when' => $activity->created_at->format('M d, Y h:i:s A').' ('.$activity->created_at->diffForHumans().')',
            'user' => self::userName($activity),
            'actionLabel' => self::eventLabel($activity->event),
            'recordName' => self::recordName($activity),
            'recordType' => $activity->subject_type ? self::typeName($activity->subject_type) : null,
            'via' => $activity->getExtraProperty('via'),
            'description' => $activity->description,
            'changes' => self::changes($activity),
            'extra' => $extra,
            'batchTotal' => $batch?->count() ?? 0,
            'batchItems' => $batch ? (clone $batch)->orderBy('id')->limit(50)->pluck('description')->all() : [],
        ];
    }

    public static function properties(Activity $activity): Collection
    {
        $properties = $activity->properties;

        return $properties instanceof Collection ? $properties : collect($properties);
    }

    public static function fieldLabel(string $key): string
    {
        $parts = explode('_', $key);

        $hasTablePrefix = count($parts) > 1
            && strlen($parts[0]) <= 5
            && ! in_array($parts[0], self::KEEP_PREFIX, true)
            && (count($parts) > 2 || $parts[1] !== 'id');

        if ($hasTablePrefix) {
            array_shift($parts);
        }

        if (count($parts) > 1 && end($parts) === 'id') {
            array_pop($parts);
        }

        $name = implode('_', $parts);

        return self::FIELD_NAMES[$name] ?? Str::headline($name);
    }

    public static function formatValue(mixed $value, string $key = ''): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (isset(self::LOOKUPS[$key]) && is_scalar($value)) {
            return self::lookup(self::LOOKUPS[$key], $value);
        }

        if (is_bool($value) || (preg_match('/(^|_)(is|has)_/', $key) && in_array($value, [0, 1, '0', '1'], true))) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return Str::limit((string) $value, 80);
    }

    /** @param class-string<Model> $class */
    private static function lookup(string $class, int|string|float $id): string
    {
        $cacheKey = $class.'#'.$id;

        if (! array_key_exists($cacheKey, self::$lookups)) {
            try {
                $model = $class::find($id);
                self::$lookups[$cacheKey] = $model ? self::subjectLabel($model) : '#'.$id;
            } catch (\Throwable) {
                self::$lookups[$cacheKey] = '#'.$id;
            }
        }

        return self::$lookups[$cacheKey];
    }

    private static function action(string $code): ?ActionModel
    {
        if (! array_key_exists($code, self::$actions)) {
            self::$actions[$code] = ActionModel::find($code);
        }

        return self::$actions[$code];
    }
}

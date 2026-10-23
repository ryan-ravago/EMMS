<?php

namespace App\Models;

use App\Models\Concerns\LogsChanges;
use Database\Factories\AssetTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetType extends Model
{
    /** @use HasFactory<AssetTypeFactory> */
    use HasFactory;

    use LogsChanges;

    /**
     * Asset types are identified by their code, never by their id or name.
     */
    public const EQUIPMENT = 'EQM';

    public const ACCESSORY = 'ACC';

    public const SERVICE_VEHICLE = 'SRVV';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * @var array<string, int>|null
     */
    protected static ?array $idsByCode = null;

    public function equipmentUnits(): HasMany
    {
        return $this->hasMany(Equipment::class, 'asset_type_id');
    }

    public function isEquipment(): bool
    {
        return $this->code === self::EQUIPMENT;
    }

    public function isAccessory(): bool
    {
        return $this->code === self::ACCESSORY;
    }

    public function isServiceVehicle(): bool
    {
        return $this->code === self::SERVICE_VEHICLE;
    }

    public static function idFor(string $code): ?int
    {
        self::$idsByCode ??= static::query()
            ->pluck('id', 'code')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        return self::$idsByCode[$code] ?? null;
    }

    public static function equipmentId(): ?int
    {
        return static::idFor(self::EQUIPMENT);
    }

    public static function accessoryId(): ?int
    {
        return static::idFor(self::ACCESSORY);
    }

    public static function serviceVehicleId(): ?int
    {
        return static::idFor(self::SERVICE_VEHICLE);
    }

    public static function codeFor(?int $id): ?string
    {
        if ($id === null) {
            return null;
        }

        static::idFor(self::EQUIPMENT);

        $code = array_search($id, self::$idsByCode ?? [], true);

        return $code === false ? null : (string) $code;
    }

    /**
     * Badge colours for the asset type picker, keyed by id.
     *
     * @return array<int, string>
     */
    public static function colorsById(): array
    {
        static::idFor(self::EQUIPMENT);

        return collect(self::$idsByCode ?? [])
            ->mapWithKeys(fn (int $id, string $code): array => [$id => match ($code) {
                self::EQUIPMENT => 'warning',
                self::ACCESSORY => 'success',
                default => 'info',
            }])
            ->all();
    }

    public static function flushIdCache(): void
    {
        self::$idsByCode = null;
    }
}

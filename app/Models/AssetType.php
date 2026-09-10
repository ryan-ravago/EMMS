<?php

namespace App\Models;

use Database\Factories\AssetTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetType extends Model
{
    /** @use HasFactory<AssetTypeFactory> */
    use HasFactory;

    public const EQUIPMENT = 'Equipment';

    public const ACCESSORY = 'Accessory';

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    /**
     * @var array<string, int>|null
     */
    protected static ?array $idsByName = null;

    public function equipmentUnits(): HasMany
    {
        return $this->hasMany(Equipment::class, 'asset_type_id');
    }

    public function isEquipment(): bool
    {
        return strcasecmp($this->name, self::EQUIPMENT) === 0;
    }

    public function isAccessory(): bool
    {
        return strcasecmp($this->name, self::ACCESSORY) === 0;
    }

    public static function idFor(string $name): ?int
    {
        self::$idsByName ??= static::query()
            ->pluck('id', 'name')
            ->mapWithKeys(fn (mixed $id, mixed $typeName): array => [strtolower((string) $typeName) => (int) $id])
            ->all();

        return self::$idsByName[strtolower($name)] ?? null;
    }

    public static function equipmentId(): ?int
    {
        return static::idFor(self::EQUIPMENT);
    }

    public static function accessoryId(): ?int
    {
        return static::idFor(self::ACCESSORY);
    }

    public static function flushIdCache(): void
    {
        self::$idsByName = null;
    }
}

<?php

namespace Database\Factories;

use App\Models\AssetType;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eqm_name' => fake()->words(3, true),
            'eqm_prc_code' => strtoupper(fake()->unique()->bothify('AST-####')),
            'eqm_is_active' => true,
            'eqm_eqmm_id' => null,
            'parent_id' => null,
        ];
    }

    public function equipment(): static
    {
        return $this->state(function (array $attributes): array {
            $type = AssetType::query()->firstOrCreate(
                ['name' => AssetType::EQUIPMENT],
            );
            AssetType::flushIdCache();

            return [
                'asset_type_id' => $type->id,
            ];
        });
    }

    public function accessory(?Equipment $parent = null): static
    {
        return $this->state(function (array $attributes) use ($parent): array {
            $type = AssetType::query()->firstOrCreate(
                ['name' => AssetType::ACCESSORY],
            );
            AssetType::flushIdCache();

            return [
                'asset_type_id' => $type->id,
                'parent_id' => $parent?->eqm_id,
            ];
        });
    }
}

<?php

namespace Database\Factories;

use App\Models\AssetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetType>
 */
class AssetTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }

    public function equipment(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => AssetType::EQUIPMENT,
        ]);
    }

    public function accessory(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => AssetType::ACCESSORY,
        ]);
    }
}

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
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'name' => fake()->unique()->word(),
        ];
    }

    public function equipment(): static
    {
        return $this->state(fn (array $attributes): array => [
            'code' => AssetType::EQUIPMENT,
            'name' => 'Equipment',
        ]);
    }

    public function accessory(): static
    {
        return $this->state(fn (array $attributes): array => [
            'code' => AssetType::ACCESSORY,
            'name' => 'Accessory',
        ]);
    }

    public function serviceVehicle(): static
    {
        return $this->state(fn (array $attributes): array => [
            'code' => AssetType::SERVICE_VEHICLE,
            'name' => 'Service Vehicle',
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\WorkOrderType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderType>
 */
class WorkOrderTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'type' => fake()->unique()->words(2, true),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\AssetMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A completed trip (both times set, nobody has verified it). The states below make it incomplete or verified.
 *
 * The asset and tag are not created here: pass asset_id and tag_id.
 *
 * @extends Factory<AssetMovement>
 */
class AssetMovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $timeOut = now()->subDays(fake()->numberBetween(1, 20))->startOfMinute();

        return [
            'time_out' => $timeOut,
            'time_in' => $timeOut->copy()->addHours(fake()->numberBetween(1, 8)),
        ];
    }

    public function missingTimeIn(): static
    {
        return $this->state(['time_in' => null]);
    }

    public function missingTimeOut(): static
    {
        return $this->state(['time_out' => null]);
    }

    public function acknowledged(int $userId, string $remarks = 'Checked'): static
    {
        return $this->state([
            'acknowledged_by' => $userId,
            'acknowledged_at' => now(),
            'acknowledgement_remarks' => $remarks,
        ]);
    }

    public function disregarded(int $userId, string $remarks = 'Not a real trip'): static
    {
        return $this->state([
            'disregarded_by' => $userId,
            'disregarded_at' => now(),
            'disregard_remarks' => $remarks,
        ]);
    }
}

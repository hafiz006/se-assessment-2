<?php

namespace Database\Factories;

use App\Enums\BarStatus;
use App\Models\Bar;
use App\Models\Deposit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bar>
 */
class BarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'deposit_id'    => Deposit::factory()->allocated(),
            'serial_number' => strtoupper($this->faker->bothify('??##########')),
            'weight_kg'     => $this->faker->randomFloat(6, 1, 12.5),
            'status'        => BarStatus::Held,
        ];
    }

    public function withdrawn(): static
    {
        return $this->state(['status' => BarStatus::Withdrawn]);
    }
}

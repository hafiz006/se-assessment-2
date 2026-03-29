<?php

namespace Database\Factories;

use App\Enums\MetalType;
use App\Models\MetalPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetalPrice>
 */
class MetalPriceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'metal_type'     => $this->faker->randomElement(MetalType::cases()),
            'price_per_kg'   => $this->faker->randomFloat(4, 1000, 100000),
            'effective_date' => today(),
        ];
    }

    public function gold(): static
    {
        return $this->state(['metal_type' => MetalType::Gold, 'price_per_kg' => 60000.0000]);
    }

    public function silver(): static
    {
        return $this->state(['metal_type' => MetalType::Silver, 'price_per_kg' => 960.0000]);
    }

    public function platinum(): static
    {
        return $this->state(['metal_type' => MetalType::Platinum, 'price_per_kg' => 30000.0000]);
    }
}

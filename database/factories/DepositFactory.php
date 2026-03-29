<?php

namespace Database\Factories;

use App\Enums\DepositStatus;
use App\Enums\MetalType;
use App\Enums\StorageType;
use App\Models\Account;
use App\Models\Deposit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deposit>
 */
class DepositFactory extends Factory
{
    protected static int $sequence = 0;

    public function definition(): array
    {
        return [
            'account_id'     => Account::factory(),
            'deposit_number' => 'DEP-' . now()->format('Ymd') . '-' . str_pad(++static::$sequence, 4, '0', STR_PAD_LEFT),
            'metal_type'     => $this->faker->randomElement(MetalType::cases()),
            'storage_type'   => StorageType::Unallocated,
            'quantity_kg'    => $this->faker->randomFloat(6, 0.1, 100),
            'status'         => DepositStatus::Confirmed,
            'confirmed_at'   => now(),
            'notes'          => null,
        ];
    }

    public function allocated(): static
    {
        return $this->state(['storage_type' => StorageType::Allocated]);
    }

    public function pending(): static
    {
        return $this->state(['status' => DepositStatus::Pending, 'confirmed_at' => null]);
    }
}

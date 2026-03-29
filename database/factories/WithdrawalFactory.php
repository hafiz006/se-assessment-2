<?php

namespace Database\Factories;

use App\Enums\WithdrawalStatus;
use App\Models\Account;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    protected static int $sequence = 0;

    public function definition(): array
    {
        $deposit = Deposit::factory()->create();

        return [
            'account_id'        => $deposit->account_id,
            'withdrawal_number' => 'WDR-' . now()->format('Ymd') . '-' . str_pad(++static::$sequence, 4, '0', STR_PAD_LEFT),
            'deposit_id'        => $deposit->id,
            'quantity_kg'       => $this->faker->randomFloat(6, 0.1, 10),
            'status'            => WithdrawalStatus::Pending,
            'processed_by'      => null,
            'notes'             => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => WithdrawalStatus::Approved]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => WithdrawalStatus::Rejected]);
    }
}

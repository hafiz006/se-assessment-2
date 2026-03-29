<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\ClientType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected static int $sequence = 0;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'account_number' => 'BM-' . str_pad(++static::$sequence, 6, '0', STR_PAD_LEFT),
            'client_type'    => ClientType::Retail,
            'status'         => AccountStatus::Active,
        ];
    }

    public function institutional(): static
    {
        return $this->state(['client_type' => ClientType::Institutional]);
    }

    public function suspended(): static
    {
        return $this->state(['status' => AccountStatus::Suspended]);
    }
}

<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\ClientType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountService
{
    public function __construct(private readonly ReferenceNumberService $referenceNumbers) {}

    public function createForUser(User $user, array $data): Account
    {
        return DB::transaction(function () use ($user, $data) {
            return Account::create([
                'user_id'        => $user->id,
                'account_number' => $this->referenceNumbers->generateAccountNumber(),
                'client_type'    => $data['client_type'] ?? ClientType::Retail,
                'status'         => AccountStatus::Active,
            ]);
        });
    }
}

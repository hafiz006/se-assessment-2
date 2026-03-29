<?php

namespace App\Policies;

use App\Models\Deposit;
use App\Models\User;

class DepositPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Deposit $deposit): bool
    {
        return $user->isAdmin() || $user->id === $deposit->account->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function confirm(User $user, Deposit $deposit): bool
    {
        return $user->isAdmin();
    }
}

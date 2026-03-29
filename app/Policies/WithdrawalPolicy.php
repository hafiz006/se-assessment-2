<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

class WithdrawalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin() || $user->id === $withdrawal->account->user_id;
    }

    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function review(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin();
    }
}

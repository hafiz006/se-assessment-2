<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Account $account): bool
    {
        return $user->isAdmin() || $user->id === $account->user_id;
    }

    public function update(User $user, Account $account): bool
    {
        return $user->isAdmin();
    }
}

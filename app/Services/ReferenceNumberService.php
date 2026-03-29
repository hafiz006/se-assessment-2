<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class ReferenceNumberService
{
    public function generateAccountNumber(): string
    {
        return DB::transaction(function () {
            $last = Account::orderByDesc('created_at')->lockForUpdate()->first();
            $seq  = $last
                ? ((int) substr($last->account_number, 3)) + 1
                : 1;

            return 'BM-' . str_pad($seq, 6, '0', STR_PAD_LEFT);
        });
    }

    public function generateDepositNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = Deposit::whereDate('created_at', today())->count() + 1;

        return 'DEP-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function generateWithdrawalNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = Withdrawal::whereDate('created_at', today())->count() + 1;

        return 'WDR-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}

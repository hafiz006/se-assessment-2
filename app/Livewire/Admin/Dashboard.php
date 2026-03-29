<?php

namespace App\Livewire\Admin;

use App\Enums\DepositStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Account;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Services\ValuationService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin Dashboard')]
class Dashboard extends Component
{
    public function render(ValuationService $valuation): View
    {
        $totalAccounts      = Account::count();
        $pendingWithdrawals = Withdrawal::where('status', WithdrawalStatus::Pending)->count();
        $depositsThisMonth  = Deposit::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // System-wide AUC per metal
        $metalSummary = [];
        foreach (\App\Enums\MetalType::cases() as $metal) {
            $quantity = Deposit::where('metal_type', $metal)
                ->whereNotIn('status', [DepositStatus::Withdrawn])
                ->sum('quantity_kg');
            try {
                $price = $valuation->latestPrice($metal);
                $value = (float) $quantity * (float) $price->price_per_kg;
            } catch (\App\Exceptions\NoPriceDataException) {
                $price = null;
                $value = 0.0;
            }
            $metalSummary[$metal->value] = [
                'quantity_kg'  => $quantity,
                'price_per_kg' => $price?->price_per_kg,
                'value_usd'    => $value,
            ];
        }

        $totalAuc = array_sum(array_column($metalSummary, 'value_usd'));

        return view('livewire.admin.dashboard', compact(
            'totalAccounts',
            'pendingWithdrawals',
            'depositsThisMonth',
            'metalSummary',
            'totalAuc',
        ));
    }
}

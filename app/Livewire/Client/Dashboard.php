<?php

namespace App\Livewire\Client;

use App\Services\ValuationService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(ValuationService $valuation): View
    {
        $user    = auth()->user();
        $account = $user->account;

        $portfolio     = $account ? $valuation->portfolioValue($account) : null;
        $recentDeposits = $account
            ? $account->deposits()->latest()->take(5)->get()
            : collect();
        $pendingWithdrawals = $account
            ? $account->withdrawals()->where('status', 'pending')->latest()->take(5)->get()
            : collect();

        return view('livewire.client.dashboard', compact('account', 'portfolio', 'recentDeposits', 'pendingWithdrawals'));
    }
}

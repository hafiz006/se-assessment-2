<?php

namespace App\Livewire\Admin\Accounts;

use App\Enums\AccountStatus;
use App\Models\Account;
use App\Services\ValuationService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Account Detail')]
class Show extends Component
{
    public Account $account;

    public function suspend(): void
    {
        $this->authorize('update', $this->account);
        $this->account->update(['status' => AccountStatus::Suspended]);
        session()->flash('status', 'Account suspended.');
    }

    public function close(): void
    {
        $this->authorize('update', $this->account);
        $this->account->update(['status' => AccountStatus::Closed]);
        session()->flash('status', 'Account closed.');
    }

    public function activate(): void
    {
        $this->authorize('update', $this->account);
        $this->account->update(['status' => AccountStatus::Active]);
        session()->flash('status', 'Account re-activated.');
    }

    public function render(ValuationService $valuation): View
    {
        $portfolio   = $valuation->portfolioValue($this->account);
        $deposits    = $this->account->deposits()->latest()->paginate(10, pageName: 'dep');
        $withdrawals = $this->account->withdrawals()->with('deposit')->latest()->paginate(10, pageName: 'wdr');

        return view('livewire.admin.accounts.show', compact('portfolio', 'deposits', 'withdrawals'));
    }
}

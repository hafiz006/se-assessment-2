<?php

namespace App\Livewire\Admin\Deposits;

use App\Models\Deposit;
use App\Services\DepositService;
use App\Services\ValuationService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Deposit Detail')]
class Show extends Component
{
    public Deposit $deposit;

    public function confirm(DepositService $depositService): void
    {
        $this->authorize('confirm', $this->deposit);
        $depositService->confirm($this->deposit);
        session()->flash('status', 'Deposit confirmed.');
    }

    public function render(ValuationService $valuation): View
    {
        $value      = null;
        $priceError = null;
        try {
            $value = $valuation->valueDeposit($this->deposit);
        } catch (\App\Exceptions\NoPriceDataException $e) {
            $priceError = $e->getMessage();
        }

        $bars        = $this->deposit->bars()->latest()->get();
        $withdrawals = $this->deposit->withdrawals()->with('processedBy')->latest()->get();

        return view('livewire.admin.deposits.show', compact('value', 'priceError', 'bars', 'withdrawals'));
    }
}

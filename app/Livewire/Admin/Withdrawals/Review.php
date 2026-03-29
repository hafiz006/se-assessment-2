<?php

namespace App\Livewire\Admin\Withdrawals;

use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Review Withdrawal')]
class Review extends Component
{
    public Withdrawal $withdrawal;

    public string $notes = '';

    public function mount(Withdrawal $withdrawal): void
    {
        $this->authorize('review', $withdrawal);
    }

    public function approve(WithdrawalService $withdrawalService): void
    {
        $this->authorize('review', $this->withdrawal);
        $withdrawalService->approve($this->withdrawal, auth()->user());
        session()->flash('status', 'Withdrawal approved.');
        $this->redirect(route('admin.withdrawals.index'), navigate: true);
    }

    public function reject(WithdrawalService $withdrawalService): void
    {
        $this->authorize('review', $this->withdrawal);
        $this->validate(['notes' => 'required|string|min:3']);
        $withdrawalService->reject($this->withdrawal, auth()->user(), $this->notes);
        session()->flash('status', 'Withdrawal rejected.');
        $this->redirect(route('admin.withdrawals.index'), navigate: true);
    }

    public function render(): View
    {
        $bars = $this->withdrawal->bars()->get();

        return view('livewire.admin.withdrawals.review', compact('bars'));
    }
}

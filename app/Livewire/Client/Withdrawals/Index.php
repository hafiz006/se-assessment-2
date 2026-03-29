<?php

namespace App\Livewire\Client\Withdrawals;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('My Withdrawals')]
class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        $account     = auth()->user()->account;
        $withdrawals = $account
            ? $account->withdrawals()->with('deposit')->latest()->paginate(15)
            : collect()->paginate(15);

        return view('livewire.client.withdrawals.index', compact('withdrawals'));
    }
}

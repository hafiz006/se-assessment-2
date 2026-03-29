<?php

namespace App\Livewire\Client\Deposits;

use App\Services\ValuationService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('My Deposits')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(ValuationService $valuation): View
    {
        $account = auth()->user()->account;

        $deposits = $account
            ? $account->deposits()
                ->when($this->search, fn ($q) => $q->where('deposit_number', 'like', '%' . $this->search . '%'))
                ->latest()
                ->paginate(15)
            : collect()->paginate(15);

        return view('livewire.client.deposits.index', compact('deposits', 'valuation'));
    }
}

<?php

namespace App\Livewire\Admin\Deposits;

use App\Models\Deposit;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Deposits')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $metalFilter = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $typeFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingMetalFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function render(): View
    {
        $deposits = Deposit::with('account.user')
            ->when($this->search, fn ($q) => $q->where('deposit_number', 'like', '%' . $this->search . '%'))
            ->when($this->metalFilter, fn ($q) => $q->where('metal_type', $this->metalFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('storage_type', $this->typeFilter))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.deposits.index', compact('deposits'));
    }
}

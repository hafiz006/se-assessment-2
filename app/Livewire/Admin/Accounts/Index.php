<?php

namespace App\Livewire\Admin\Accounts;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Account;

#[Layout('layouts.app')]
#[Title('Accounts')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $accounts = Account::with('user')
            ->when($this->search, fn ($q) => $q
                ->where('account_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.accounts.index', compact('accounts'));
    }
}

<?php

namespace App\Livewire\Admin\Withdrawals;

use App\Enums\WithdrawalStatus;
use App\Models\Withdrawal;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Withdrawal Requests')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $statusFilter = 'pending';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $withdrawals = Withdrawal::with(['account.user', 'deposit'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(20);

        $statuses = WithdrawalStatus::cases();

        return view('livewire.admin.withdrawals.index', compact('withdrawals', 'statuses'));
    }
}

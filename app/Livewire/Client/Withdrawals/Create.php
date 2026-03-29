<?php

namespace App\Livewire\Client\Withdrawals;

use App\Enums\DepositStatus;
use App\Enums\StorageType;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Deposit;
use App\Services\WithdrawalService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Request Withdrawal')]
class Create extends Component
{
    #[Validate('required|exists:deposits,id')]
    public string $depositId = '';

    #[Validate('nullable|numeric|min:0.000001')]
    public string $quantityKg = '';

    /** @var string[] */
    public array $selectedBarIds = [];

    public string $notes = '';

    public ?Deposit $selectedDeposit = null;

    public function updatedDepositId(): void
    {
        $this->selectedDeposit = $this->depositId
            ? Deposit::find($this->depositId)
            : null;
        $this->selectedBarIds = [];
        $this->quantityKg     = '';
    }

    public function save(WithdrawalService $withdrawalService): void
    {
        $this->validate();

        $deposit = Deposit::findOrFail($this->depositId);
        $this->authorize('view', $deposit);

        $data = ['notes' => $this->notes ?: null];

        if ($deposit->storage_type === StorageType::Unallocated) {
            $this->validate(['quantityKg' => 'required|numeric|min:0.000001']);
            $data['quantity_kg'] = $this->quantityKg;
        } else {
            $this->validate(['selectedBarIds' => 'required|array|min:1']);
            $data['bar_ids'] = $this->selectedBarIds;
        }

        try {
            $withdrawal = $withdrawalService->request($deposit, $data, auth()->user());
            session()->flash('status', "Withdrawal {$withdrawal->withdrawal_number} submitted.");
            $this->redirect(route('withdrawals.index'), navigate: true);
        } catch (InsufficientBalanceException $e) {
            $this->addError('quantityKg', $e->getMessage());
        }
    }

    public function render(): View
    {
        $account = auth()->user()->account;
        $deposits = $account
            ? $account->deposits()
                ->whereIn('status', [DepositStatus::Confirmed])
                ->get()
            : collect();

        return view('livewire.client.withdrawals.create', compact('deposits'));
    }
}

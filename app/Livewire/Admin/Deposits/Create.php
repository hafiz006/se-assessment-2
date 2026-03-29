<?php

namespace App\Livewire\Admin\Deposits;

use App\Enums\ClientType;
use App\Enums\MetalType;
use App\Enums\StorageType;
use App\Exceptions\StorageTypeMismatchException;
use App\Models\Account;
use App\Services\DepositService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Create Deposit')]
class Create extends Component
{
    #[Validate('required|exists:accounts,id')]
    public string $accountId = '';

    #[Validate('required|in:gold,silver,platinum')]
    public string $metalType = '';

    #[Validate('required|in:allocated,unallocated')]
    public string $storageType = '';

    #[Validate('required|numeric|min:0.000001')]
    public string $quantityKg = '';

    public string $notes = '';

    /** @var array<array{serial_number: string, weight_kg: string}> */
    public array $bars = [];

    public function mount(): void
    {
        $this->bars = [['serial_number' => '', 'weight_kg' => '']];
    }

    public function addBar(): void
    {
        $this->bars[] = ['serial_number' => '', 'weight_kg' => ''];
    }

    public function removeBar(int $index): void
    {
        array_splice($this->bars, $index, 1);
    }

    public function save(DepositService $depositService): void
    {
        $this->validate();

        $account = Account::findOrFail($this->accountId);

        if ($this->storageType === StorageType::Allocated->value) {
            $this->validate([
                'bars'                 => 'required|array|min:1',
                'bars.*.serial_number' => 'required|string|distinct',
                'bars.*.weight_kg'     => 'required|numeric|min:0.000001',
            ]);

            $barTotal = array_sum(array_column($this->bars, 'weight_kg'));
            if (abs($barTotal - (float) $this->quantityKg) > 0.000001) {
                $this->addError('bars', 'Bar weight total must equal deposit quantity.');
                return;
            }
        }

        try {
            $deposit = $depositService->create($account, [
                'metal_type'   => $this->metalType,
                'storage_type' => $this->storageType,
                'quantity_kg'  => $this->quantityKg,
                'notes'        => $this->notes ?: null,
                'bars'         => $this->bars,
            ]);

            session()->flash('status', "Deposit {$deposit->deposit_number} created.");
            $this->redirect(route('admin.deposits.show', $deposit), navigate: true);
        } catch (StorageTypeMismatchException $e) {
            $this->addError('storageType', $e->getMessage());
        }
    }

    public function render(): View
    {
        $accounts    = Account::with('user')->orderBy('account_number')->get();
        $metalTypes  = MetalType::cases();
        $selectedAccount = $this->accountId ? Account::find($this->accountId) : null;
        $isRetail = $selectedAccount?->client_type === ClientType::Retail;

        return view('livewire.admin.deposits.create', compact('accounts', 'metalTypes', 'isRetail'));
    }
}

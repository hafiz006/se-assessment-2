<?php

namespace App\Livewire\Client\Deposits;

use App\Enums\MetalType;
use App\Enums\StorageType;
use App\Exceptions\StorageTypeMismatchException;
use App\Services\DepositService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('New Deposit')]
class Create extends Component
{
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

    public function updatedStorageType(): void
    {
        if ($this->storageType === StorageType::Allocated->value && empty($this->bars)) {
            $this->bars = [['serial_number' => '', 'weight_kg' => '']];
        }
    }

    public function save(DepositService $depositService): void
    {
        $this->validate();

        if ($this->storageType === StorageType::Allocated->value) {
            $this->validate([
                'bars'                      => 'required|array|min:1',
                'bars.*.serial_number'      => 'required|string|distinct',
                'bars.*.weight_kg'          => 'required|numeric|min:0.000001',
            ]);

            // Validate sum of bar weights equals quantity_kg
            $barTotal = array_sum(array_column($this->bars, 'weight_kg'));
            if (abs($barTotal - (float) $this->quantityKg) > 0.000001) {
                $this->addError('bars', 'The total bar weight (' . $barTotal . ' kg) must equal the deposit quantity (' . $this->quantityKg . ' kg).');
                return;
            }
        }

        $account = auth()->user()->account;

        try {
            $deposit = $depositService->create($account, [
                'metal_type'   => $this->metalType,
                'storage_type' => $this->storageType,
                'quantity_kg'  => $this->quantityKg,
                'notes'        => $this->notes ?: null,
                'bars'         => $this->bars,
            ]);

            session()->flash('status', "Deposit {$deposit->deposit_number} submitted successfully.");
            $this->redirect(route('deposits.show', $deposit), navigate: true);
        } catch (StorageTypeMismatchException $e) {
            $this->addError('storageType', $e->getMessage());
        }
    }

    public function render(): View
    {
        $account     = auth()->user()->account;
        $metalTypes  = MetalType::cases();
        $isRetail    = $account?->client_type?->value === 'retail';

        return view('livewire.client.deposits.create', compact('metalTypes', 'isRetail'));
    }
}

<?php

namespace App\Services;

use App\Enums\BarStatus;
use App\Enums\ClientType;
use App\Enums\DepositStatus;
use App\Enums\StorageType;
use App\Exceptions\StorageTypeMismatchException;
use App\Models\Account;
use App\Models\Bar;
use App\Models\Deposit;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepositService
{
    public function __construct(private readonly ReferenceNumberService $referenceNumbers) {}

    /**
     * Create a new deposit (and bars if allocated).
     *
     * @param  array{
     *   metal_type: string,
     *   storage_type: string,
     *   quantity_kg: float,
     *   notes?: string|null,
     *   bars?: array<array{serial_number: string, weight_kg: float}>
     * } $data
     */
    public function create(Account $account, array $data): Deposit
    {
        if ($data['storage_type'] === StorageType::Allocated->value
            && $account->client_type !== ClientType::Institutional
        ) {
            throw new StorageTypeMismatchException(
                'Only institutional clients can create allocated deposits.'
            );
        }

        return DB::transaction(function () use ($account, $data) {
            $deposit = Deposit::create([
                'account_id'     => $account->id,
                'deposit_number' => $this->referenceNumbers->generateDepositNumber(),
                'metal_type'     => $data['metal_type'],
                'storage_type'   => $data['storage_type'],
                'quantity_kg'    => $data['quantity_kg'],
                'status'         => DepositStatus::Pending,
                'notes'          => $data['notes'] ?? null,
            ]);

            if ($data['storage_type'] === StorageType::Allocated->value) {
                foreach ($data['bars'] as $barData) {
                    try {
                        Bar::create([
                            'deposit_id'    => $deposit->id,
                            'serial_number' => $barData['serial_number'],
                            'weight_kg'     => $barData['weight_kg'],
                            'status'        => BarStatus::Held,
                        ]);
                    } catch (UniqueConstraintViolationException) {
                        throw ValidationException::withMessages([
                            'bars' => "Bar serial number '{$barData['serial_number']}' is already registered in the system.",
                        ]);
                    }
                }
            }

            return $deposit;
        });
    }

    /**
     * Confirm a pending deposit (admin action).
     */
    public function confirm(Deposit $deposit): void
    {
        $deposit->update([
            'status'       => DepositStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }
}

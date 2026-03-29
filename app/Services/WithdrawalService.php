<?php

namespace App\Services;

use App\Enums\BarStatus;
use App\Enums\DepositStatus;
use App\Enums\StorageType;
use App\Enums\WithdrawalStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Bar;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\WithdrawalBar;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    public function __construct(private readonly ReferenceNumberService $referenceNumbers) {}

    /**
     * Create a withdrawal request.
     *
     * @param  array{
     *   quantity_kg?: float,
     *   bar_ids?: string[],
     *   notes?: string|null
     * } $data
     */
    public function request(Deposit $deposit, array $data, User $requester): Withdrawal
    {
        return DB::transaction(function () use ($deposit, $data, $requester) {
            // Re-fetch with pessimistic lock to prevent concurrent race conditions (edge case #7)
            $deposit = Deposit::lockForUpdate()->findOrFail($deposit->id);

            if ($deposit->storage_type === StorageType::Unallocated) {
                $available = $deposit->availableQuantityKg();

                if ((float) $data['quantity_kg'] > $available) {
                    throw new InsufficientBalanceException(
                        "Insufficient balance. Available: {$available} kg, requested: {$data['quantity_kg']} kg."
                    );
                }
            } else {
                // Allocated — validate bars belong to this deposit and are held (edge case #5)
                $barIds      = $data['bar_ids'];
                $availableBars = $deposit->bars()
                    ->whereIn('id', $barIds)
                    ->where('status', BarStatus::Held)
                    ->count();

                if ($availableBars !== count($barIds)) {
                    throw new InsufficientBalanceException(
                        'One or more requested bars are not available for withdrawal.'
                    );
                }
            }

            $withdrawal = Withdrawal::create([
                'withdrawal_number' => $this->referenceNumbers->generateWithdrawalNumber(),
                'account_id'        => $deposit->account_id,
                'deposit_id'        => $deposit->id,
                'quantity_kg'       => $deposit->storage_type === StorageType::Unallocated
                    ? $data['quantity_kg']
                    : null,
                'status'            => WithdrawalStatus::Pending,
                'notes'             => $data['notes'] ?? null,
            ]);

            if ($deposit->storage_type === StorageType::Allocated) {
                foreach ($data['bar_ids'] as $barId) {
                    WithdrawalBar::create([
                        'withdrawal_id' => $withdrawal->id,
                        'bar_id'        => $barId,
                    ]);
                }
            }

            return $withdrawal;
        });
    }

    /**
     * Approve a pending withdrawal (admin action).
     */
    public function approve(Withdrawal $withdrawal, User $admin): void
    {
        DB::transaction(function () use ($withdrawal, $admin) {
            $withdrawal->update([
                'status'       => WithdrawalStatus::Approved,
                'processed_by' => $admin->id,
            ]);

            if ($withdrawal->deposit->storage_type === StorageType::Allocated) {
                // Mark bars as withdrawn (edge case #5)
                Bar::whereIn('id', $withdrawal->bars()->pluck('bars.id'))->update([
                    'status' => BarStatus::Withdrawn,
                ]);

                // If every bar on the deposit is now withdrawn, close the deposit
                $heldCount = $withdrawal->deposit->bars()->where('status', BarStatus::Held)->count();
                if ($heldCount === 0) {
                    $withdrawal->deposit->update(['status' => DepositStatus::Withdrawn]);
                }
            }
        });
    }

    /**
     * Reject a pending withdrawal (admin action).
     */
    public function reject(Withdrawal $withdrawal, User $admin, string $reason): void
    {
        $withdrawal->update([
            'status'       => WithdrawalStatus::Rejected,
            'processed_by' => $admin->id,
            'notes'        => $reason,
        ]);
    }
}

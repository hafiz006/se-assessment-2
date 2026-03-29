<?php

namespace App\Models;

use App\Enums\DepositStatus;
use App\Enums\MetalType;
use App\Enums\StorageType;
use Database\Factories\DepositFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['account_id', 'deposit_number', 'metal_type', 'storage_type', 'quantity_kg', 'status', 'confirmed_at', 'notes'])]
class Deposit extends Model
{
    /** @use HasFactory<DepositFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'metal_type'   => MetalType::class,
            'storage_type' => StorageType::class,
            'status'       => DepositStatus::class,
            'quantity_kg'  => 'decimal:6',
            'confirmed_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function bars(): HasMany
    {
        return $this->hasMany(Bar::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Quantity already approved/completed for withdrawal (unallocated deposits).
     */
    public function withdrawnQuantityKg(): float
    {
        return (float) $this->withdrawals()
            ->whereIn('status', ['approved', 'completed'])
            ->sum('quantity_kg');
    }

    /**
     * Available quantity remaining (unallocated deposits).
     */
    public function availableQuantityKg(): float
    {
        return (float) $this->quantity_kg - $this->withdrawnQuantityKg();
    }
}

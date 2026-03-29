<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Database\Factories\WithdrawalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['account_id', 'withdrawal_number', 'deposit_id', 'quantity_kg', 'status', 'processed_by', 'notes'])]
class Withdrawal extends Model
{
    /** @use HasFactory<WithdrawalFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'status'      => WithdrawalStatus::class,
            'quantity_kg' => 'decimal:6',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function withdrawalBars(): HasMany
    {
        return $this->hasMany(WithdrawalBar::class);
    }

    public function bars(): BelongsToMany
    {
        return $this->belongsToMany(Bar::class, 'withdrawal_bars');
    }
}

<?php

namespace App\Models;

use App\Enums\BarStatus;
use Database\Factories\BarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['deposit_id', 'serial_number', 'weight_kg', 'status'])]
class Bar extends Model
{
    /** @use HasFactory<BarFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'status'    => BarStatus::class,
            'weight_kg' => 'decimal:6',
        ];
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function withdrawalBars(): HasMany
    {
        return $this->hasMany(WithdrawalBar::class);
    }
}

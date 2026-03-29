<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['withdrawal_id', 'bar_id'])]
class WithdrawalBar extends Model
{
    use HasUlids;

    public $timestamps = false;

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function bar(): BelongsTo
    {
        return $this->belongsTo(Bar::class);
    }
}

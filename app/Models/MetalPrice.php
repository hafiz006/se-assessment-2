<?php

namespace App\Models;

use App\Enums\MetalType;
use Database\Factories\MetalPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['metal_type', 'price_per_kg', 'effective_date'])]
class MetalPrice extends Model
{
    /** @use HasFactory<MetalPriceFactory> */
    use HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'metal_type'     => MetalType::class,
            'price_per_kg'   => 'decimal:4',
            'effective_date' => 'date',
        ];
    }
}

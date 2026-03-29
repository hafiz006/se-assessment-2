<?php

namespace Database\Seeders;

use App\Enums\MetalType;
use App\Models\MetalPrice;
use Illuminate\Database\Seeder;

class MetalPriceSeeder extends Seeder
{
    public function run(): void
    {
        $prices = [
            [MetalType::Gold,     60000.0000],
            [MetalType::Silver,     960.0000],
            [MetalType::Platinum, 30000.0000],
        ];

        foreach ($prices as [$metal, $price]) {
            MetalPrice::updateOrCreate(
                ['metal_type' => $metal, 'effective_date' => today()],
                ['price_per_kg' => $price],
            );
        }
    }
}

<?php

namespace App\Services;

use App\Enums\DepositStatus;
use App\Enums\MetalType;
use App\Exceptions\NoPriceDataException;
use App\Models\Account;
use App\Models\Deposit;
use App\Models\MetalPrice;

class ValuationService
{
    /**
     * Retrieve the latest stored price for a metal type (edge case #4).
     *
     * Falls back to the most recent price if today's price is missing.
     * Throws NoPriceDataException if the table is completely empty for that metal.
     */
    public function latestPrice(MetalType $metalType): MetalPrice
    {
        $price = MetalPrice::where('metal_type', $metalType)
            ->orderByDesc('effective_date')
            ->first();

        if (! $price) {
            throw new NoPriceDataException(
                "No price data available for {$metalType->value}."
            );
        }

        return $price;
    }

    /**
     * Calculate the current USD value of a single deposit.
     */
    public function valueDeposit(Deposit $deposit): float
    {
        $price = $this->latestPrice($deposit->metal_type);

        return (float) $deposit->quantity_kg * (float) $price->price_per_kg;
    }

    /**
     * Calculate portfolio valuation for an account, grouped by metal type.
     *
     * @return array{
     *   gold: array{metal_type: MetalType, quantity_kg: float, price_per_kg: string|null, value_usd: float},
     *   silver: array{...},
     *   platinum: array{...},
     *   total_usd: float
     * }
     */
    public function portfolioValue(Account $account): array
    {
        $deposits = $account->deposits()
            ->whereNotIn('status', [DepositStatus::Withdrawn])
            ->get();

        $result = [];
        $total  = 0.0;

        foreach (MetalType::cases() as $metalType) {
            $metalDeposits = $deposits->filter(fn (Deposit $d) => $d->metal_type === $metalType);
            $quantity      = (float) $metalDeposits->sum('quantity_kg');

            try {
                $price = $this->latestPrice($metalType);
                $value = $quantity * (float) $price->price_per_kg;
            } catch (NoPriceDataException) {
                $price = null;
                $value = 0.0;
            }

            $result[$metalType->value] = [
                'metal_type'   => $metalType,
                'quantity_kg'  => $quantity,
                'price_per_kg' => $price?->price_per_kg,
                'value_usd'    => $value,
            ];

            $total += $value;
        }

        $result['total_usd'] = $total;

        return $result;
    }
}

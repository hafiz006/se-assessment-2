<?php

use App\Enums\ClientType;
use App\Enums\DepositStatus;
use App\Enums\MetalType;
use App\Enums\StorageType;
use App\Enums\UserRole;
use App\Exceptions\NoPriceDataException;
use App\Models\Account;
use App\Models\Deposit;
use App\Models\MetalPrice;
use App\Models\User;
use App\Services\ValuationService;

test('valueDeposit returns quantity multiplied by price_per_kg', function () {
    MetalPrice::factory()->silver()->create(); // $960/kg

    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type'   => MetalType::Silver,
        'storage_type' => StorageType::Unallocated,
        'quantity_kg'  => 5.0,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);

    $value = app(ValuationService::class)->valueDeposit($deposit);
    expect($value)->toEqual(4800.0);
});

test('latestPrice returns most recent price when multiple exist', function () {
    MetalPrice::factory()->gold()->create(['effective_date' => today()->subDays(10), 'price_per_kg' => 55000]);
    MetalPrice::factory()->gold()->create(['effective_date' => today(), 'price_per_kg' => 60000]);
    MetalPrice::factory()->gold()->create(['effective_date' => today()->subDays(5), 'price_per_kg' => 58000]);

    $price = app(ValuationService::class)->latestPrice(MetalType::Gold);
    expect((float) $price->price_per_kg)->toEqual(60000.0);
});

test('latestPrice throws NoPriceDataException when no record exists', function () {
    expect(fn () => app(ValuationService::class)->latestPrice(MetalType::Platinum))
        ->toThrow(NoPriceDataException::class);
});

test('portfolioValue returns correct total across multiple metals', function () {
    MetalPrice::factory()->gold()->create();
    MetalPrice::factory()->silver()->create();
    MetalPrice::factory()->platinum()->create();

    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);

    // Gold: 1 kg × $60,000 = $60,000
    Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 1.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);
    // Silver: 10 kg × $960 = $9,600
    Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Silver, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 10.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);
    // Platinum: 2 kg × $30,000 = $60,000
    Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Platinum, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 2.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);

    $portfolio = app(ValuationService::class)->portfolioValue($account);

    expect($portfolio['gold']['value_usd'])->toEqual(60000.0);
    expect($portfolio['silver']['value_usd'])->toEqual(9600.0);
    expect($portfolio['platinum']['value_usd'])->toEqual(60000.0);
    expect($portfolio['total_usd'])->toEqual(129600.0);
});

test('portfolioValue excludes withdrawn deposits', function () {
    MetalPrice::factory()->gold()->create();

    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);

    // Active deposit
    Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 2.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);
    // Withdrawn deposit — should not count
    Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 3.0, 'status' => DepositStatus::Withdrawn, 'confirmed_at' => now(),
    ]);

    $portfolio = app(ValuationService::class)->portfolioValue($account);
    expect($portfolio['gold']['quantity_kg'])->toEqual(2.0);
    expect($portfolio['gold']['value_usd'])->toEqual(120000.0);
});

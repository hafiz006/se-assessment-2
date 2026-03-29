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

test('valueDeposit returns correct value', function () {
    MetalPrice::factory()->gold()->create(); // $60,000/kg

    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type'   => MetalType::Gold,
        'storage_type' => StorageType::Unallocated,
        'quantity_kg'  => 2.0,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);

    $value = app(ValuationService::class)->valueDeposit($deposit);

    expect($value)->toEqual(120000.0);
});

test('portfolioValue groups by metal type', function () {
    MetalPrice::factory()->gold()->create();
    MetalPrice::factory()->silver()->create();
    MetalPrice::factory()->platinum()->create();

    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);

    Deposit::factory()->for($account)->create([
        'metal_type'   => MetalType::Gold,
        'storage_type' => StorageType::Unallocated,
        'quantity_kg'  => 1.0,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);
    Deposit::factory()->for($account)->create([
        'metal_type'   => MetalType::Silver,
        'storage_type' => StorageType::Unallocated,
        'quantity_kg'  => 10.0,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);

    $portfolio = app(ValuationService::class)->portfolioValue($account);

    expect($portfolio['gold']['value_usd'])->toEqual(60000.0);
    expect($portfolio['silver']['value_usd'])->toEqual(9600.0);
    expect($portfolio['total_usd'])->toEqual(69600.0);
});

test('portfolioValue falls back to most recent price rather than failing', function () {
    // Insert a price from yesterday
    MetalPrice::factory()->gold()->create(['effective_date' => today()->subDay()]);

    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    Deposit::factory()->for($account)->create([
        'metal_type'   => MetalType::Gold,
        'storage_type' => StorageType::Unallocated,
        'quantity_kg'  => 1.0,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);

    $portfolio = app(ValuationService::class)->portfolioValue($account);
    expect($portfolio['gold']['value_usd'])->toEqual(60000.0);
});

test('latestPrice throws NoPriceDataException when no prices exist', function () {
    expect(fn () => app(ValuationService::class)->latestPrice(MetalType::Platinum))
        ->toThrow(NoPriceDataException::class);
});

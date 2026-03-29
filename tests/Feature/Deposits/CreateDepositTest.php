<?php

use App\Enums\AccountStatus;
use App\Enums\ClientType;
use App\Enums\DepositStatus;
use App\Enums\MetalType;
use App\Enums\StorageType;
use App\Enums\UserRole;
use App\Exceptions\StorageTypeMismatchException;
use App\Models\Account;
use App\Models\Deposit;
use App\Models\MetalPrice;
use App\Models\User;
use App\Services\DepositService;

beforeEach(function () {
    MetalPrice::factory()->gold()->create();
    MetalPrice::factory()->silver()->create();
});

test('client can create an unallocated deposit', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);

    $deposit = app(DepositService::class)->create($account, [
        'metal_type'   => 'gold',
        'storage_type' => 'unallocated',
        'quantity_kg'  => 5.0,
    ]);

    expect($deposit)
        ->deposit_number->toStartWith('DEP-')
        ->metal_type->toBe(MetalType::Gold)
        ->storage_type->toBe(StorageType::Unallocated)
        ->status->toBe(DepositStatus::Pending);
});

test('retail client cannot create an allocated deposit via service', function () {
    $user = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);

    $service = app(DepositService::class);

    expect(fn () => $service->create($account, [
        'metal_type'   => 'gold',
        'storage_type' => 'allocated',
        'quantity_kg'  => 12.441310,
        'bars'         => [['serial_number' => 'SN001', 'weight_kg' => 12.441310]],
    ]))->toThrow(StorageTypeMismatchException::class);
});

test('institutional client can create allocated deposit with bars', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);

    $service = app(DepositService::class);

    $deposit = $service->create($account, [
        'metal_type'   => 'gold',
        'storage_type' => 'allocated',
        'quantity_kg'  => 24.882620,
        'bars'         => [
            ['serial_number' => 'GOLD-TEST-001', 'weight_kg' => 12.441310],
            ['serial_number' => 'GOLD-TEST-002', 'weight_kg' => 12.441310],
        ],
    ]);

    expect($deposit->bars)->toHaveCount(2);
    expect($deposit->bars->sum('weight_kg'))->toEqual(24.882620);
});

test('duplicate bar serial number triggers validation error', function () {
    $user = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);

    $service = app(DepositService::class);

    // Create first deposit with the serial number
    $service->create($account, [
        'metal_type'   => 'gold',
        'storage_type' => 'allocated',
        'quantity_kg'  => 12.441310,
        'bars'         => [['serial_number' => 'DUPLICATE-001', 'weight_kg' => 12.441310]],
    ]);

    // Attempt to create another with the same serial number
    expect(fn () => $service->create($account, [
        'metal_type'   => 'gold',
        'storage_type' => 'allocated',
        'quantity_kg'  => 12.441310,
        'bars'         => [['serial_number' => 'DUPLICATE-001', 'weight_kg' => 12.441310]],
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});

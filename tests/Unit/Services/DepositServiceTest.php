<?php

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
use App\Services\ReferenceNumberService;

beforeEach(function () {
    MetalPrice::factory()->gold()->create();
    MetalPrice::factory()->silver()->create();
});

test('create sets status to pending', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);

    $service = app(DepositService::class);
    $deposit = $service->create($account, [
        'metal_type'   => 'gold',
        'storage_type' => 'unallocated',
        'quantity_kg'  => 5.0,
    ]);

    expect($deposit->status)->toBe(DepositStatus::Pending);
    expect($deposit->confirmed_at)->toBeNull();
});

test('create assigns a unique deposit number', function () {
    $user     = User::factory()->create(['role' => UserRole::Client]);
    $account  = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $account2 = Account::factory()->for(User::factory()->create())->create(['client_type' => ClientType::Retail]);

    $service = app(DepositService::class);
    $dep1    = $service->create($account, ['metal_type' => 'gold', 'storage_type' => 'unallocated', 'quantity_kg' => 1.0]);
    $dep2    = $service->create($account2, ['metal_type' => 'silver', 'storage_type' => 'unallocated', 'quantity_kg' => 2.0]);

    expect($dep1->deposit_number)->not->toBe($dep2->deposit_number);
});

test('confirm sets status to confirmed and records confirmed_at', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create(['status' => DepositStatus::Pending]);

    app(DepositService::class)->confirm($deposit);

    $deposit->refresh();
    expect($deposit->status)->toBe(DepositStatus::Confirmed);
    expect($deposit->confirmed_at)->not->toBeNull();
});

test('allocated deposit creates bar records', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);

    $deposit = app(DepositService::class)->create($account, [
        'metal_type'   => 'gold',
        'storage_type' => 'allocated',
        'quantity_kg'  => 24.882620,
        'bars'         => [
            ['serial_number' => 'SN-UNIT-01', 'weight_kg' => 12.441310],
            ['serial_number' => 'SN-UNIT-02', 'weight_kg' => 12.441310],
        ],
    ]);

    expect($deposit->bars)->toHaveCount(2);
});

test('retail account cannot create allocated deposit', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);

    expect(fn () => app(DepositService::class)->create($account, [
        'metal_type'   => 'gold',
        'storage_type' => 'allocated',
        'quantity_kg'  => 5.0,
        'bars'         => [['serial_number' => 'SN-001', 'weight_kg' => 5.0]],
    ]))->toThrow(StorageTypeMismatchException::class);
});

<?php

use App\Enums\BarStatus;
use App\Enums\ClientType;
use App\Enums\DepositStatus;
use App\Enums\MetalType;
use App\Enums\StorageType;
use App\Enums\UserRole;
use App\Enums\WithdrawalStatus;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Account;
use App\Models\Bar;
use App\Models\Deposit;
use App\Models\MetalPrice;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\DepositService;
use App\Services\WithdrawalService;

beforeEach(function () {
    MetalPrice::factory()->gold()->create();
});

test('client can request unallocated withdrawal within available balance', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type'   => MetalType::Gold,
        'storage_type' => StorageType::Unallocated,
        'quantity_kg'  => 10.0,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);

    $withdrawal = app(WithdrawalService::class)->request($deposit, [
        'quantity_kg' => 3.0,
    ], $user);

    expect($withdrawal->status)->toBe(WithdrawalStatus::Pending);
    expect($withdrawal->quantity_kg)->toEqual(3.0);
});

test('withdrawal exceeding available balance throws exception', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type'   => MetalType::Gold,
        'storage_type' => StorageType::Unallocated,
        'quantity_kg'  => 5.0,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);

    expect(fn () => app(WithdrawalService::class)->request($deposit, [
        'quantity_kg' => 10.0,
    ], $user))->toThrow(InsufficientBalanceException::class);
});

test('bar withdrawal marks bar as withdrawn and closes deposit when all bars withdrawn', function () {
    $admin   = User::factory()->create(['role' => UserRole::Admin]);
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type'   => MetalType::Gold,
        'storage_type' => StorageType::Allocated,
        'quantity_kg'  => 12.441310,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);
    $bar = Bar::factory()->for($deposit)->create(['weight_kg' => 12.441310, 'status' => BarStatus::Held]);

    $service    = app(WithdrawalService::class);
    $withdrawal = $service->request($deposit, ['bar_ids' => [$bar->id]], $user);
    $service->approve($withdrawal, $admin);

    expect($bar->fresh()->status)->toBe(BarStatus::Withdrawn);
    expect($deposit->fresh()->status)->toBe(DepositStatus::Withdrawn);
});

test('withdrawal for bar not belonging to deposit is rejected', function () {
    $user     = User::factory()->create(['role' => UserRole::Client]);
    $account  = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);
    $deposit  = Deposit::factory()->for($account)->create([
        'storage_type' => StorageType::Allocated,
        'quantity_kg'  => 12.441310,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);
    $otherDeposit = Deposit::factory()->for($account)->create([
        'storage_type' => StorageType::Allocated,
        'quantity_kg'  => 12.441310,
        'status'       => DepositStatus::Confirmed,
        'confirmed_at' => now(),
    ]);
    $foreignBar = Bar::factory()->for($otherDeposit)->create(['status' => BarStatus::Held]);

    expect(fn () => app(WithdrawalService::class)->request($deposit, [
        'bar_ids' => [$foreignBar->id],
    ], $user))->toThrow(InsufficientBalanceException::class);
});

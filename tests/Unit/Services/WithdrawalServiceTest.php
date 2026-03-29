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
use App\Services\WithdrawalService;

beforeEach(function () {
    MetalPrice::factory()->gold()->create();
});

test('request creates withdrawal with pending status', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 10.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);

    $withdrawal = app(WithdrawalService::class)->request($deposit, ['quantity_kg' => 4.0], $user);

    expect($withdrawal->status)->toBe(WithdrawalStatus::Pending);
    expect((float) $withdrawal->quantity_kg)->toEqual(4.0);
});

test('partial withdrawal updates available balance correctly', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $admin   = User::factory()->create(['role' => UserRole::Admin]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 10.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);

    $service = app(WithdrawalService::class);

    // First withdrawal: 4 kg
    $w1 = $service->request($deposit, ['quantity_kg' => 4.0], $user);
    $service->approve($w1, $admin);

    // Available: 10 - 4 = 6 kg. Request 5 should succeed
    $w2 = $service->request($deposit->fresh(), ['quantity_kg' => 5.0], $user);
    expect($w2->status)->toBe(WithdrawalStatus::Pending);

    // Available: 10 - 4 = 6, pending 5. Approved so far: 4.
    // After approving w2: available = 10 - 4 - 5 = 1. Requesting 2 should fail.
    $service->approve($w2, $admin);
    expect(fn () => $service->request($deposit->fresh(), ['quantity_kg' => 2.0], $user))
        ->toThrow(InsufficientBalanceException::class);
});

test('reject sets status to rejected and stores reason', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $admin   = User::factory()->create(['role' => UserRole::Admin]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 10.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);

    $service    = app(WithdrawalService::class);
    $withdrawal = $service->request($deposit, ['quantity_kg' => 3.0], $user);
    $service->reject($withdrawal, $admin, 'Insufficient documentation');

    expect($withdrawal->fresh()->status)->toBe(WithdrawalStatus::Rejected);
    expect($withdrawal->fresh()->notes)->toBe('Insufficient documentation');
});

test('allocated withdrawal links bars to withdrawal_bars pivot', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Allocated,
        'quantity_kg' => 12.441310, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);
    $bar = Bar::factory()->for($deposit)->create(['weight_kg' => 12.441310, 'status' => BarStatus::Held]);

    $withdrawal = app(WithdrawalService::class)->request($deposit, ['bar_ids' => [$bar->id]], $user);

    expect($withdrawal->bars)->toHaveCount(1);
    expect($withdrawal->bars->first()->id)->toBe($bar->id);
});

test('withdrawal for already withdrawn bar fails', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $admin   = User::factory()->create(['role' => UserRole::Admin]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Allocated,
        'quantity_kg' => 24.882620, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);
    $bar1 = Bar::factory()->for($deposit)->create(['weight_kg' => 12.441310, 'status' => BarStatus::Held]);
    $bar2 = Bar::factory()->for($deposit)->create(['weight_kg' => 12.441310, 'status' => BarStatus::Held]);

    $service = app(WithdrawalService::class);
    $w1      = $service->request($deposit, ['bar_ids' => [$bar1->id]], $user);
    $service->approve($w1, $admin); // bar1 is now Withdrawn

    // Trying to withdraw bar1 again should fail
    expect(fn () => $service->request($deposit->fresh(), ['bar_ids' => [$bar1->id]], $user))
        ->toThrow(InsufficientBalanceException::class);
});

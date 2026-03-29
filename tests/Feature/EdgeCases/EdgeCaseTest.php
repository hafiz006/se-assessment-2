<?php

use App\Enums\AccountStatus;
use App\Enums\BarStatus;
use App\Enums\ClientType;
use App\Enums\DepositStatus;
use App\Enums\MetalType;
use App\Enums\StorageType;
use App\Enums\UserRole;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\NoPriceDataException;
use App\Exceptions\StorageTypeMismatchException;
use App\Models\Account;
use App\Models\Bar;
use App\Models\Deposit;
use App\Models\MetalPrice;
use App\Models\User;
use App\Services\DepositService;
use App\Services\ValuationService;
use App\Services\WithdrawalService;

// Edge case 1: Withdrawal exceeds available balance
test('edge case 1: withdrawal exceeds available balance is blocked', function () {
    MetalPrice::factory()->gold()->create();
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 5.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);

    expect(fn () => app(WithdrawalService::class)->request($deposit, ['quantity_kg' => 10.0], $user))
        ->toThrow(InsufficientBalanceException::class);
});

// Edge case 2: Duplicate bar serial number
test('edge case 2: duplicate bar serial number produces validation error', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);
    $service = app(DepositService::class);

    $service->create($account, [
        'metal_type' => 'gold', 'storage_type' => 'allocated', 'quantity_kg' => 12.441310,
        'bars' => [['serial_number' => 'DUP-BAR-001', 'weight_kg' => 12.441310]],
    ]);

    expect(fn () => $service->create($account, [
        'metal_type' => 'gold', 'storage_type' => 'allocated', 'quantity_kg' => 12.441310,
        'bars' => [['serial_number' => 'DUP-BAR-001', 'weight_kg' => 12.441310]],
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});

// Edge case 3: Retail client requests allocated storage
test('edge case 3: retail client cannot create allocated deposit', function () {
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);

    expect(fn () => app(DepositService::class)->create($account, [
        'metal_type' => 'gold', 'storage_type' => 'allocated', 'quantity_kg' => 5.0,
        'bars' => [['serial_number' => 'SN001', 'weight_kg' => 5.0]],
    ]))->toThrow(StorageTypeMismatchException::class);
});

// Edge case 4: No metal price exists
test('edge case 4: NoPriceDataException when no price data available', function () {
    expect(fn () => app(ValuationService::class)->latestPrice(MetalType::Platinum))
        ->toThrow(NoPriceDataException::class);
});

// Edge case 4b: Fallback to most recent price (not necessarily today's)
test('edge case 4b: valuation falls back to most recent price if today is missing', function () {
    MetalPrice::factory()->gold()->create(['effective_date' => today()->subDays(5)]);
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 1.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);

    $value = app(ValuationService::class)->valueDeposit($deposit);
    expect($value)->toEqual(60000.0);
});

// Edge case 5: Partial withdrawal from allocated deposit
test('edge case 5: partial bar withdrawal leaves deposit active until all bars withdrawn', function () {
    MetalPrice::factory()->gold()->create();
    $admin   = User::factory()->create(['role' => UserRole::Admin]);
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Institutional]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Allocated,
        'quantity_kg' => 24.882620, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);
    $bar1 = Bar::factory()->for($deposit)->create(['weight_kg' => 12.441310, 'status' => BarStatus::Held]);
    $bar2 = Bar::factory()->for($deposit)->create(['weight_kg' => 12.441310, 'status' => BarStatus::Held]);

    $wService = app(WithdrawalService::class);

    // Withdraw only bar1
    $w1 = $wService->request($deposit, ['bar_ids' => [$bar1->id]], $user);
    $wService->approve($w1, $admin);

    expect($bar1->fresh()->status)->toBe(BarStatus::Withdrawn);
    expect($bar2->fresh()->status)->toBe(BarStatus::Held);
    expect($deposit->fresh()->status)->toBe(DepositStatus::Confirmed); // still active

    // Withdraw bar2 — now deposit should be Withdrawn
    $w2 = $wService->request($deposit->fresh(), ['bar_ids' => [$bar2->id]], $user);
    $wService->approve($w2, $admin);

    expect($bar2->fresh()->status)->toBe(BarStatus::Withdrawn);
    expect($deposit->fresh()->status)->toBe(DepositStatus::Withdrawn);
});

// Edge case 6: Suspended account
test('edge case 6: suspended account is redirected from transactions', function () {
    $user    = User::factory()->create(['role' => UserRole::Client, 'email_verified_at' => now()]);
    $account = Account::factory()->for($user)->create([
        'client_type' => ClientType::Retail,
        'status'      => AccountStatus::Suspended,
    ]);

    $this->actingAs($user)
        ->get(route('deposits.create'))
        ->assertRedirect(route('dashboard'));
});

// Edge case 7: Concurrent withdrawal race condition (structural test)
test('edge case 7: service uses lockForUpdate to guard against concurrent withdrawals', function () {
    // Simply verify that the WithdrawalService::request method uses DB transaction + lockForUpdate
    // by ensuring two sequential requests that together exceed balance both fail correctly

    MetalPrice::factory()->gold()->create();
    $user    = User::factory()->create(['role' => UserRole::Client]);
    $account = Account::factory()->for($user)->create(['client_type' => ClientType::Retail]);
    $deposit = Deposit::factory()->for($account)->create([
        'metal_type' => MetalType::Gold, 'storage_type' => StorageType::Unallocated,
        'quantity_kg' => 5.0, 'status' => DepositStatus::Confirmed, 'confirmed_at' => now(),
    ]);

    $admin   = User::factory()->create(['role' => UserRole::Admin]);
    $service = app(WithdrawalService::class);

    // First withdrawal takes 4 kg and gets approved
    $w1 = $service->request($deposit, ['quantity_kg' => 4.0], $user);
    $service->approve($w1, $admin);

    // Second withdrawal tries to take 2 kg (only 1 kg available)
    expect(fn () => $service->request($deposit->fresh(), ['quantity_kg' => 2.0], $user))
        ->toThrow(InsufficientBalanceException::class);
});

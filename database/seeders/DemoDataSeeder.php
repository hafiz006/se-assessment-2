<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\BarStatus;
use App\Enums\ClientType;
use App\Enums\DepositStatus;
use App\Enums\MetalType;
use App\Enums\StorageType;
use App\Enums\UserRole;
use App\Models\Account;
use App\Models\Bar;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Retail client
        $retail = User::updateOrCreate(
            ['email' => 'retail@example.com'],
            [
                'name'              => 'Alice Retail',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Client,
                'email_verified_at' => now(),
            ],
        );

        $retailAccount = Account::updateOrCreate(
            ['user_id' => $retail->id],
            [
                'account_number' => 'BM-000001',
                'client_type'    => ClientType::Retail,
                'status'         => AccountStatus::Active,
            ],
        );

        // Institutional client
        $institutional = User::updateOrCreate(
            ['email' => 'institutional@example.com'],
            [
                'name'              => 'Bob Institutional',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Client,
                'email_verified_at' => now(),
            ],
        );

        $institutionalAccount = Account::updateOrCreate(
            ['user_id' => $institutional->id],
            [
                'account_number' => 'BM-000002',
                'client_type'    => ClientType::Institutional,
                'status'         => AccountStatus::Active,
            ],
        );

        // Extra retail clients
        foreach (['carol@example.com' => ['Carol Retail', 'BM-000003'], 'dave@example.com' => ['Dave Retail', 'BM-000004']] as $email => [$name, $num]) {
            $u = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'password'          => Hash::make('password'),
                    'role'              => UserRole::Client,
                    'email_verified_at' => now(),
                ],
            );
            Account::updateOrCreate(
                ['user_id' => $u->id],
                ['account_number' => $num, 'client_type' => ClientType::Retail, 'status' => AccountStatus::Active],
            );
        }

        // Retail unallocated deposit (gold)
        $retailDeposit = Deposit::updateOrCreate(
            ['deposit_number' => 'DEP-20260329-0001'],
            [
                'account_id'   => $retailAccount->id,
                'metal_type'   => MetalType::Gold,
                'storage_type' => StorageType::Unallocated,
                'quantity_kg'  => 5.000000,
                'status'       => DepositStatus::Confirmed,
                'confirmed_at' => now(),
                'notes'        => 'Initial gold deposit',
            ],
        );

        // Retail silver unallocated deposit
        Deposit::updateOrCreate(
            ['deposit_number' => 'DEP-20260329-0002'],
            [
                'account_id'   => $retailAccount->id,
                'metal_type'   => MetalType::Silver,
                'storage_type' => StorageType::Unallocated,
                'quantity_kg'  => 50.000000,
                'status'       => DepositStatus::Confirmed,
                'confirmed_at' => now(),
                'notes'        => null,
            ],
        );

        // Institutional allocated gold deposit
        $allocatedDeposit = Deposit::updateOrCreate(
            ['deposit_number' => 'DEP-20260329-0003'],
            [
                'account_id'   => $institutionalAccount->id,
                'metal_type'   => MetalType::Gold,
                'storage_type' => StorageType::Allocated,
                'quantity_kg'  => 25.000000,
                'status'       => DepositStatus::Confirmed,
                'confirmed_at' => now(),
                'notes'        => 'Institutional allocated gold',
            ],
        );

        // Bars for institutional deposit
        $barData = [
            ['GOLD-BAR-00001', 12.441310],
            ['GOLD-BAR-00002', 12.558690],
        ];

        foreach ($barData as [$serial, $weight]) {
            Bar::updateOrCreate(
                ['serial_number' => $serial],
                [
                    'deposit_id' => $allocatedDeposit->id,
                    'weight_kg'  => $weight,
                    'status'     => BarStatus::Held,
                ],
            );
        }

        // Institutional platinum allocated deposit
        $platDeposit = Deposit::updateOrCreate(
            ['deposit_number' => 'DEP-20260329-0004'],
            [
                'account_id'   => $institutionalAccount->id,
                'metal_type'   => MetalType::Platinum,
                'storage_type' => StorageType::Allocated,
                'quantity_kg'  => 10.000000,
                'status'       => DepositStatus::Confirmed,
                'confirmed_at' => now(),
                'notes'        => null,
            ],
        );

        Bar::updateOrCreate(
            ['serial_number' => 'PLAT-BAR-00001'],
            ['deposit_id' => $platDeposit->id, 'weight_kg' => 5.000000, 'status' => BarStatus::Held],
        );
        Bar::updateOrCreate(
            ['serial_number' => 'PLAT-BAR-00002'],
            ['deposit_id' => $platDeposit->id, 'weight_kg' => 5.000000, 'status' => BarStatus::Held],
        );
    }
}

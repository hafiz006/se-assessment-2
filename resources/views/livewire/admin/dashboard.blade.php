<flux:heading size="xl" class="mb-6">Admin Dashboard</flux:heading>

{{-- Stats row --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <flux:card>
        <flux:text class="text-zinc-500 text-sm">Active Accounts</flux:text>
        <p class="text-3xl font-bold mt-1">{{ $totalAccounts }}</p>
    </flux:card>
    <flux:card>
        <flux:text class="text-zinc-500 text-sm">Pending Withdrawals</flux:text>
        <p class="text-3xl font-bold mt-1 @if($pendingWithdrawals > 0) text-amber-500 @endif">{{ $pendingWithdrawals }}</p>
    </flux:card>
    <flux:card>
        <flux:text class="text-zinc-500 text-sm">Deposits This Month</flux:text>
        <p class="text-3xl font-bold mt-1">{{ $depositsThisMonth }}</p>
    </flux:card>
</div>

{{-- AUC by metal --}}
<flux:card class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <flux:heading>Assets Under Custody</flux:heading>
        <p class="font-bold text-lg">${{ number_format($totalAuc, 2) }}</p>
    </div>
    <flux:table>
        <flux:columns>
            <flux:column>Metal</flux:column>
            <flux:column>Quantity (kg)</flux:column>
            <flux:column>Spot Price ($/kg)</flux:column>
            <flux:column>Value (USD)</flux:column>
        </flux:columns>
        <flux:rows>
            @foreach ($metalSummary as $metal => $data)
                <flux:row>
                    <flux:cell class="font-semibold capitalize">{{ $metal }}</flux:cell>
                    <flux:cell>{{ number_format((float) $data['quantity_kg'], 6) }}</flux:cell>
                    <flux:cell>{{ $data['price_per_kg'] ? '$' . number_format($data['price_per_kg'], 2) : '—' }}</flux:cell>
                    <flux:cell>${{ number_format($data['value_usd'], 2) }}</flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>
</flux:card>

{{-- Quick links --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <flux:button href="{{ route('admin.accounts.index') }}" variant="outline" wire:navigate class="h-16">Accounts</flux:button>
    <flux:button href="{{ route('admin.deposits.index') }}" variant="outline" wire:navigate class="h-16">Deposits</flux:button>
    <flux:button href="{{ route('admin.withdrawals.index') }}" variant="outline" wire:navigate class="h-16">Withdrawals</flux:button>
    <flux:button href="{{ route('admin.prices.index') }}" variant="outline" wire:navigate class="h-16">Metal Prices</flux:button>
</div>

<div>
@if (session('error'))
    <flux:callout variant="danger" class="mb-4">{{ session('error') }}</flux:callout>
@endif

@if (! $account)
    <flux:callout variant="danger">Your account is not yet set up. Please contact support.</flux:callout>
@else
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Welcome back, {{ auth()->user()->name }}</flux:heading>
            <flux:text class="text-zinc-500">{{ $account->account_number }} · {{ ucfirst($account->client_type->value) }}</flux:text>
        </div>
    </div>

    {{-- Portfolio total --}}
    @if ($portfolio)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <flux:card class="col-span-1 sm:col-span-2 lg:col-span-1">
                <flux:heading>Total Portfolio Value</flux:heading>
                <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100 mt-1">
                    ${{ number_format($portfolio['total_usd'], 2) }}
                </p>
            </flux:card>

            @foreach (\App\Enums\MetalType::cases() as $metal)
                @php $m = $portfolio[$metal->value]; @endphp
                <flux:card>
                    <flux:heading>{{ ucfirst($metal->value) }}</flux:heading>
                    <p class="text-lg font-semibold mt-1">${{ number_format($m['value_usd'], 2) }}</p>
                    <flux:text class="text-xs text-zinc-500">{{ number_format($m['quantity_kg'], 6) }} kg</flux:text>
                </flux:card>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent deposits --}}
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading>Recent Deposits</flux:heading>
                <flux:button href="{{ route('deposits.index') }}" size="sm" variant="ghost" wire:navigate>View all</flux:button>
            </div>
            @forelse ($recentDeposits as $deposit)
                <div class="flex justify-between items-center py-2 border-b dark:border-zinc-700 last:border-0">
                    <div>
                        <p class="font-medium text-sm">{{ $deposit->deposit_number }}</p>
                        <p class="text-xs text-zinc-500">{{ ucfirst($deposit->metal_type->value) }} · {{ ucfirst($deposit->storage_type->value) }}</p>
                    </div>
                    <x-deposit-status-badge :status="$deposit->status" />
                </div>
            @empty
                <flux:text class="text-zinc-500 text-sm">No deposits yet.</flux:text>
            @endforelse
        </flux:card>

        {{-- Pending withdrawals --}}
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading>Pending Withdrawals</flux:heading>
                <flux:button href="{{ route('withdrawals.index') }}" size="sm" variant="ghost" wire:navigate>View all</flux:button>
            </div>
            @forelse ($pendingWithdrawals as $w)
                <div class="flex justify-between items-center py-2 border-b dark:border-zinc-700 last:border-0">
                    <p class="font-medium text-sm">{{ $w->withdrawal_number }}</p>
                    <span class="text-xs text-amber-600 font-semibold uppercase">Pending</span>
                </div>
            @empty
                <flux:text class="text-zinc-500 text-sm">No pending withdrawals.</flux:text>
            @endforelse
        </flux:card>
    </div>
@endif
</div>

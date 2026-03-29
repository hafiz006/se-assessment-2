<div class="mb-6 flex items-center gap-2">
    <flux:button href="{{ route('admin.accounts.index') }}" icon="arrow-left" size="sm" variant="ghost" wire:navigate />
    <flux:heading size="xl">{{ $account->account_number }}</flux:heading>
    <x-account-status-badge :status="$account->status" />
</div>

@if (session('status'))
    <flux:callout variant="success" class="mb-4">{{ session('status') }}</flux:callout>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Account info --}}
    <flux:card class="lg:col-span-2">
        <flux:heading class="mb-3">Client Information</flux:heading>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-zinc-500">Name</dt><dd class="font-medium">{{ $account->user->name }}</dd></div>
            <div><dt class="text-zinc-500">Email</dt><dd>{{ $account->user->email }}</dd></div>
            <div><dt class="text-zinc-500">Type</dt><dd class="capitalize">{{ $account->client_type->value }}</dd></div>
            <div><dt class="text-zinc-500">Portfolio</dt><dd class="font-bold">${{ number_format($portfolio['total_usd'], 2) }}</dd></div>
        </dl>
    </flux:card>

    {{-- Account actions --}}
    <flux:card>
        <flux:heading class="mb-3">Account Actions</flux:heading>
        <div class="space-y-2">
            @if ($account->status->value !== 'active')
                <flux:button wire:click="activate" variant="primary" class="w-full" wire:confirm="Re-activate this account?">Activate</flux:button>
            @endif
            @if ($account->status->value === 'active')
                <flux:button wire:click="suspend" variant="warning" class="w-full" wire:confirm="Suspend this account?">Suspend</flux:button>
            @endif
            @if ($account->status->value !== 'closed')
                <flux:button wire:click="close" variant="danger" class="w-full" wire:confirm="Permanently close this account?">Close Account</flux:button>
            @endif
        </div>
    </flux:card>
</div>

{{-- Portfolio by metal --}}
<flux:card class="mb-6">
    <flux:heading class="mb-3">Portfolio Breakdown</flux:heading>
    <flux:table>
        <flux:columns>
            <flux:column>Metal</flux:column>
            <flux:column>Quantity (kg)</flux:column>
            <flux:column>Spot Price</flux:column>
            <flux:column>Value (USD)</flux:column>
        </flux:columns>
        <flux:rows>
            @foreach (\App\Enums\MetalType::cases() as $metal)
                @php $m = $portfolio[$metal->value]; @endphp
                <flux:row>
                    <flux:cell class="capitalize font-medium">{{ $metal->value }}</flux:cell>
                    <flux:cell>{{ number_format($m['quantity_kg'], 6) }}</flux:cell>
                    <flux:cell>{{ $m['price_per_kg'] ? '$' . number_format($m['price_per_kg'], 2) : '—' }}</flux:cell>
                    <flux:cell>${{ number_format($m['value_usd'], 2) }}</flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>
</flux:card>

{{-- Deposits --}}
<flux:card class="mb-6">
    <flux:heading class="mb-3">Deposits</flux:heading>
    <flux:table>
        <flux:columns>
            <flux:column>Number</flux:column>
            <flux:column>Metal</flux:column>
            <flux:column>Storage</flux:column>
            <flux:column>Quantity</flux:column>
            <flux:column>Status</flux:column>
            <flux:column></flux:column>
        </flux:columns>
        <flux:rows>
            @forelse ($deposits as $dep)
                <flux:row :key="$dep->id">
                    <flux:cell class="font-mono text-sm">{{ $dep->deposit_number }}</flux:cell>
                    <flux:cell class="capitalize">{{ $dep->metal_type->value }}</flux:cell>
                    <flux:cell class="capitalize">{{ $dep->storage_type->value }}</flux:cell>
                    <flux:cell>{{ number_format($dep->quantity_kg, 6) }}</flux:cell>
                    <flux:cell><x-deposit-status-badge :status="$dep->status" /></flux:cell>
                    <flux:cell>
                        <flux:button size="xs" href="{{ route('admin.deposits.show', $dep) }}" wire:navigate>View</flux:button>
                    </flux:cell>
                </flux:row>
            @empty
                <flux:row><flux:cell colspan="6" class="py-4 text-center text-zinc-500">No deposits.</flux:cell></flux:row>
            @endforelse
        </flux:rows>
    </flux:table>
    <div class="mt-3">{{ $deposits->links() }}</div>
</flux:card>

{{-- Withdrawals --}}
<flux:card>
    <flux:heading class="mb-3">Withdrawals</flux:heading>
    <flux:table>
        <flux:columns>
            <flux:column>Number</flux:column>
            <flux:column>Deposit</flux:column>
            <flux:column>Status</flux:column>
            <flux:column>Date</flux:column>
        </flux:columns>
        <flux:rows>
            @forelse ($withdrawals as $w)
                <flux:row :key="$w->id">
                    <flux:cell class="font-mono text-sm">{{ $w->withdrawal_number }}</flux:cell>
                    <flux:cell class="font-mono text-sm">{{ $w->deposit->deposit_number }}</flux:cell>
                    <flux:cell><x-withdrawal-status-badge :status="$w->status" /></flux:cell>
                    <flux:cell class="text-xs text-zinc-500">{{ $w->created_at->format('d M Y') }}</flux:cell>
                </flux:row>
            @empty
                <flux:row><flux:cell colspan="4" class="py-4 text-center text-zinc-500">No withdrawals.</flux:cell></flux:row>
            @endforelse
        </flux:rows>
    </flux:table>
    <div class="mt-3">{{ $withdrawals->links() }}</div>
</flux:card>

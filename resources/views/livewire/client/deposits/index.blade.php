@if (session('status'))
    <flux:callout variant="success" class="mb-4">{{ session('status') }}</flux:callout>
@endif

<div class="flex items-center justify-between mb-6">
    <flux:heading size="xl">My Deposits</flux:heading>
    <flux:button href="{{ route('deposits.create') }}" variant="primary" wire:navigate icon="plus">New Deposit</flux:button>
</div>

<flux:card>
    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by deposit number…" icon="magnifying-glass" />
    </div>

    <flux:table>
        <flux:columns>
            <flux:column>Number</flux:column>
            <flux:column>Metal</flux:column>
            <flux:column>Type</flux:column>
            <flux:column>Quantity (kg)</flux:column>
            <flux:column>Status</flux:column>
            <flux:column>Est. Value</flux:column>
            <flux:column>Date</flux:column>
            <flux:column></flux:column>
        </flux:columns>
        <flux:rows>
            @forelse ($deposits as $deposit)
                <flux:row :key="$deposit->id">
                    <flux:cell class="font-mono text-sm">{{ $deposit->deposit_number }}</flux:cell>
                    <flux:cell>{{ ucfirst($deposit->metal_type->value) }}</flux:cell>
                    <flux:cell>{{ ucfirst($deposit->storage_type->value) }}</flux:cell>
                    <flux:cell>{{ number_format($deposit->quantity_kg, 6) }}</flux:cell>
                    <flux:cell><x-deposit-status-badge :status="$deposit->status" /></flux:cell>
                    <flux:cell>
                        @php
                            try {
                                echo '$' . number_format($valuation->valueDeposit($deposit), 2);
                            } catch (\App\Exceptions\NoPriceDataException) {
                                echo '<span class="text-zinc-400 text-xs">N/A</span>';
                            }
                        @endphp
                    </flux:cell>
                    <flux:cell class="text-zinc-500 text-xs">{{ $deposit->created_at->format('d M Y') }}</flux:cell>
                    <flux:cell>
                        <flux:button size="xs" href="{{ route('deposits.show', $deposit) }}" wire:navigate>View</flux:button>
                    </flux:cell>
                </flux:row>
            @empty
                <flux:row>
                    <flux:cell colspan="8" class="text-center text-zinc-500 py-8">No deposits found.</flux:cell>
                </flux:row>
            @endforelse
        </flux:rows>
    </flux:table>

    <div class="mt-4">
        {{ $deposits->links() }}
    </div>
</flux:card>

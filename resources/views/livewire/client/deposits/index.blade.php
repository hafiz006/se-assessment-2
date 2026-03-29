<div>
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
        <flux:table.columns>
            <flux:table.column>Number</flux:table.column>
            <flux:table.column>Metal</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Quantity (kg)</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Est. Value</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($deposits as $deposit)
                <flux:table.row :key="$deposit->id">
                    <flux:table.cell class="font-mono text-sm">{{ $deposit->deposit_number }}</flux:table.cell>
                    <flux:table.cell>{{ ucfirst($deposit->metal_type->value) }}</flux:table.cell>
                    <flux:table.cell>{{ ucfirst($deposit->storage_type->value) }}</flux:table.cell>
                    <flux:table.cell>{{ number_format($deposit->quantity_kg, 6) }}</flux:table.cell>
                    <flux:table.cell><x-deposit-status-badge :status="$deposit->status" /></flux:table.cell>
                    <flux:table.cell>
                        @php
                            try {
                                echo '$' . number_format($valuation->valueDeposit($deposit), 2);
                            } catch (\App\Exceptions\NoPriceDataException) {
                                echo '<span class="text-zinc-400 text-xs">N/A</span>';
                            }
                        @endphp
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-xs">{{ $deposit->created_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="xs" href="{{ route('deposits.show', $deposit) }}" wire:navigate>View</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center text-zinc-500 py-8">No deposits found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $deposits->links() }}
    </div>
</flux:card>
</div>

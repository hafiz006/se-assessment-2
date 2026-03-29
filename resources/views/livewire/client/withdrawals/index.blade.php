@if (session('status'))
    <flux:callout variant="success" class="mb-4">{{ session('status') }}</flux:callout>
@endif

<div class="flex items-center justify-between mb-6">
    <flux:heading size="xl">My Withdrawals</flux:heading>
    <flux:button href="{{ route('withdrawals.create') }}" variant="primary" icon="plus" wire:navigate>Request Withdrawal</flux:button>
</div>

<flux:card>
    <flux:table>
        <flux:columns>
            <flux:column>Number</flux:column>
            <flux:column>Deposit</flux:column>
            <flux:column>Quantity (kg)</flux:column>
            <flux:column>Status</flux:column>
            <flux:column>Submitted</flux:column>
        </flux:columns>
        <flux:rows>
            @forelse ($withdrawals as $w)
                <flux:row :key="$w->id">
                    <flux:cell class="font-mono text-sm">{{ $w->withdrawal_number }}</flux:cell>
                    <flux:cell class="text-sm">{{ $w->deposit->deposit_number }}</flux:cell>
                    <flux:cell>{{ $w->quantity_kg ? number_format($w->quantity_kg, 6) : 'Bars' }}</flux:cell>
                    <flux:cell><x-withdrawal-status-badge :status="$w->status" /></flux:cell>
                    <flux:cell class="text-xs text-zinc-500">{{ $w->created_at->format('d M Y') }}</flux:cell>
                </flux:row>
            @empty
                <flux:row>
                    <flux:cell colspan="5" class="text-center py-8 text-zinc-500">No withdrawal requests yet.</flux:cell>
                </flux:row>
            @endforelse
        </flux:rows>
    </flux:table>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</flux:card>

<div>
@if (session('status'))
    <flux:callout variant="success" class="mb-4">{{ session('status') }}</flux:callout>
@endif

<div class="flex items-center justify-between mb-6">
    <flux:heading size="xl">My Withdrawals</flux:heading>
    <flux:button href="{{ route('withdrawals.create') }}" variant="primary" icon="plus" wire:navigate>Request Withdrawal</flux:button>
</div>

<flux:card>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Number</flux:table.column>
            <flux:table.column>Deposit</flux:table.column>
            <flux:table.column>Quantity (kg)</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Submitted</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($withdrawals as $w)
                <flux:table.row :key="$w->id">
                    <flux:table.cell class="font-mono text-sm">{{ $w->withdrawal_number }}</flux:table.cell>
                    <flux:table.cell class="text-sm">{{ $w->deposit->deposit_number }}</flux:table.cell>
                    <flux:table.cell>{{ $w->quantity_kg ? number_format($w->quantity_kg, 6) : 'Bars' }}</flux:table.cell>
                    <flux:table.cell><x-withdrawal-status-badge :status="$w->status" /></flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-500">{{ $w->created_at->format('d M Y') }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-8 text-zinc-500">No withdrawal requests yet.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</flux:card>
</div>

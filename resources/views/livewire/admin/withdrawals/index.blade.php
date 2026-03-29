<div>
<flux:heading size="xl" class="mb-6">Withdrawal Requests</flux:heading>

<flux:card>
    <div class="mb-4">
        <flux:radio.group wire:model.live="statusFilter" variant="segmented">
            <flux:radio value="">All</flux:radio>
            @foreach ($statuses as $s)
                <flux:radio value="{{ $s->value }}">{{ ucfirst($s->value) }}</flux:radio>
            @endforeach
        </flux:radio.group>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Number</flux:table.column>
            <flux:table.column>Account</flux:table.column>
            <flux:table.column>Deposit</flux:table.column>
            <flux:table.column>Quantity</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Submitted</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($withdrawals as $w)
                <flux:table.row :key="$w->id" @class(['bg-amber-50 dark:bg-amber-900/10' => $w->status->value === 'pending'])>
                    <flux:table.cell class="font-mono text-sm">{{ $w->withdrawal_number }}</flux:table.cell>
                    <flux:table.cell class="text-sm">{{ $w->account->account_number }}</flux:table.cell>
                    <flux:table.cell class="font-mono text-sm">{{ $w->deposit->deposit_number }}</flux:table.cell>
                    <flux:table.cell>{{ $w->quantity_kg ? number_format($w->quantity_kg, 6) . ' kg' : 'Bars' }}</flux:table.cell>
                    <flux:table.cell><x-withdrawal-status-badge :status="$w->status" /></flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-500">{{ $w->created_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($w->status->value === 'pending')
                            <flux:button size="xs" variant="primary" href="{{ route('admin.withdrawals.review', $w) }}" wire:navigate>Review</flux:button>
                        @else
                            <flux:button size="xs" variant="ghost" href="{{ route('admin.withdrawals.review', $w) }}" wire:navigate>View</flux:button>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center py-8 text-zinc-500">No withdrawal requests found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</flux:card>
</div>

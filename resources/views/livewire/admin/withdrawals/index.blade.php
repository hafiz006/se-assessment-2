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
        <flux:columns>
            <flux:column>Number</flux:column>
            <flux:column>Account</flux:column>
            <flux:column>Deposit</flux:column>
            <flux:column>Quantity</flux:column>
            <flux:column>Status</flux:column>
            <flux:column>Submitted</flux:column>
            <flux:column></flux:column>
        </flux:columns>
        <flux:rows>
            @forelse ($withdrawals as $w)
                <flux:row :key="$w->id" @class(['bg-amber-50 dark:bg-amber-900/10' => $w->status->value === 'pending'])>
                    <flux:cell class="font-mono text-sm">{{ $w->withdrawal_number }}</flux:cell>
                    <flux:cell class="text-sm">{{ $w->account->account_number }}</flux:cell>
                    <flux:cell class="font-mono text-sm">{{ $w->deposit->deposit_number }}</flux:cell>
                    <flux:cell>{{ $w->quantity_kg ? number_format($w->quantity_kg, 6) . ' kg' : 'Bars' }}</flux:cell>
                    <flux:cell><x-withdrawal-status-badge :status="$w->status" /></flux:cell>
                    <flux:cell class="text-xs text-zinc-500">{{ $w->created_at->format('d M Y') }}</flux:cell>
                    <flux:cell>
                        @if ($w->status->value === 'pending')
                            <flux:button size="xs" variant="primary" href="{{ route('admin.withdrawals.review', $w) }}" wire:navigate>Review</flux:button>
                        @else
                            <flux:button size="xs" variant="ghost" href="{{ route('admin.withdrawals.review', $w) }}" wire:navigate>View</flux:button>
                        @endif
                    </flux:cell>
                </flux:row>
            @empty
                <flux:row>
                    <flux:cell colspan="7" class="text-center py-8 text-zinc-500">No withdrawal requests found.</flux:cell>
                </flux:row>
            @endforelse
        </flux:rows>
    </flux:table>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</flux:card>

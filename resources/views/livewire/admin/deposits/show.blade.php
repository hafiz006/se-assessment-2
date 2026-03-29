<div>
<div class="mb-6 flex items-center gap-2">
    <flux:button href="{{ route('admin.deposits.index') }}" icon="arrow-left" size="sm" variant="ghost" wire:navigate />
    <flux:heading size="xl">{{ $deposit->deposit_number }}</flux:heading>
    <x-deposit-status-badge :status="$deposit->status" />
</div>

@if (session('status'))
    <flux:callout variant="success" class="mb-4">{{ session('status') }}</flux:callout>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <flux:card class="lg:col-span-2">
        <flux:heading class="mb-4">Deposit Details</flux:heading>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-zinc-500">Account</dt><dd class="font-mono">{{ $deposit->account->account_number }}</dd></div>
            <div><dt class="text-zinc-500">Client</dt><dd>{{ $deposit->account->user->name }}</dd></div>
            <div><dt class="text-zinc-500">Metal</dt><dd class="font-medium capitalize">{{ $deposit->metal_type->value }}</dd></div>
            <div><dt class="text-zinc-500">Storage</dt><dd class="capitalize">{{ $deposit->storage_type->value }}</dd></div>
            <div><dt class="text-zinc-500">Quantity</dt><dd class="font-medium">{{ number_format($deposit->quantity_kg, 6) }} kg</dd></div>
            <div>
                <dt class="text-zinc-500">Est. Value</dt>
                <dd class="font-medium">
                    @if ($priceError)
                        <span class="text-amber-500 text-xs">{{ $priceError }}</span>
                    @else
                        ${{ number_format($value, 2) }}
                    @endif
                </dd>
            </div>
            <div><dt class="text-zinc-500">Created</dt><dd>{{ $deposit->created_at->format('d M Y') }}</dd></div>
            <div><dt class="text-zinc-500">Confirmed</dt><dd>{{ $deposit->confirmed_at?->format('d M Y') ?? '—' }}</dd></div>
            @if ($deposit->notes)
                <div class="col-span-2"><dt class="text-zinc-500">Notes</dt><dd>{{ $deposit->notes }}</dd></div>
            @endif
        </dl>
    </flux:card>

    <flux:card>
        <flux:heading class="mb-4">Actions</flux:heading>
        @if ($deposit->status->value === 'pending')
            <flux:button wire:click="confirm" variant="primary" class="w-full" wire:confirm="Confirm this deposit?">
                Confirm Deposit
            </flux:button>
        @else
            <flux:text class="text-zinc-500 text-sm">No actions available.</flux:text>
        @endif
    </flux:card>
</div>

@if ($deposit->storage_type->value === 'allocated')
    <flux:card class="mb-6">
        <flux:heading class="mb-4">Bars ({{ $bars->count() }})</flux:heading>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Serial Number</flux:table.column>
                <flux:table.column>Weight (kg)</flux:table.column>
                <flux:table.column>Status</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($bars as $bar)
                    <flux:table.row :key="$bar->id">
                        <flux:table.cell class="font-mono text-sm">{{ $bar->serial_number }}</flux:table.cell>
                        <flux:table.cell>{{ number_format($bar->weight_kg, 6) }}</flux:table.cell>
                        <flux:table.cell>
                            <span @class(['text-xs font-semibold capitalize', 'text-green-600' => $bar->status->value === 'held', 'text-zinc-400' => $bar->status->value === 'withdrawn'])>
                                {{ $bar->status->value }}
                            </span>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row><flux:table.cell colspan="3" class="text-center py-4 text-zinc-500">No bars.</flux:table.cell></flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
@endif

<flux:card>
    <flux:heading class="mb-4">Withdrawal History</flux:heading>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Number</flux:table.column>
            <flux:table.column>Quantity (kg)</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Processed By</flux:table.column>
            <flux:table.column>Date</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($withdrawals as $w)
                <flux:table.row :key="$w->id">
                    <flux:table.cell class="font-mono text-sm">{{ $w->withdrawal_number }}</flux:table.cell>
                    <flux:table.cell>{{ $w->quantity_kg ? number_format($w->quantity_kg, 6) : '—' }}</flux:table.cell>
                    <flux:table.cell><x-withdrawal-status-badge :status="$w->status" /></flux:table.cell>
                    <flux:table.cell>{{ $w->processedBy?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-500">{{ $w->created_at->format('d M Y') }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row><flux:table.cell colspan="5" class="text-center py-4 text-zinc-500">No withdrawals.</flux:table.cell></flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
</div>

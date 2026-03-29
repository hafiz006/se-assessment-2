<div class="mb-6 flex items-center gap-2">
    <flux:button href="{{ route('admin.withdrawals.index') }}" icon="arrow-left" size="sm" variant="ghost" wire:navigate />
    <flux:heading size="xl">Review Withdrawal</flux:heading>
    <x-withdrawal-status-badge :status="$withdrawal->status" />
</div>

@if (session('status'))
    <flux:callout variant="success" class="mb-4">{{ session('status') }}</flux:callout>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Withdrawal info --}}
    <flux:card>
        <flux:heading class="mb-4">Withdrawal Details</flux:heading>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-zinc-500">Number</dt><dd class="font-mono">{{ $withdrawal->withdrawal_number }}</dd></div>
            <div><dt class="text-zinc-500">Account</dt><dd class="font-mono">{{ $withdrawal->account->account_number }}</dd></div>
            <div><dt class="text-zinc-500">Deposit</dt><dd class="font-mono">{{ $withdrawal->deposit->deposit_number }}</dd></div>
            <div><dt class="text-zinc-500">Metal</dt><dd class="capitalize">{{ $withdrawal->deposit->metal_type->value }}</dd></div>
            @if ($withdrawal->quantity_kg)
                <div><dt class="text-zinc-500">Quantity</dt><dd class="font-medium">{{ number_format($withdrawal->quantity_kg, 6) }} kg</dd></div>
            @endif
            @if ($withdrawal->notes)
                <div class="col-span-2"><dt class="text-zinc-500">Client Notes</dt><dd>{{ $withdrawal->notes }}</dd></div>
            @endif
        </dl>
    </flux:card>

    {{-- Approve / Reject --}}
    @if ($withdrawal->status->value === 'pending')
        <flux:card>
            <flux:heading class="mb-4">Decision</flux:heading>

            @if ($bars->count() > 0)
                <div class="mb-4">
                    <flux:heading size="sm" class="mb-2">Bars Requested</flux:heading>
                    @foreach ($bars as $bar)
                        <div class="flex justify-between py-1 border-b dark:border-zinc-700 last:border-0 text-sm">
                            <span class="font-mono">{{ $bar->serial_number }}</span>
                            <span class="text-zinc-500">{{ number_format($bar->weight_kg, 6) }} kg</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="space-y-3">
                <flux:button wire:click="approve" variant="primary" class="w-full" wire:confirm="Approve this withdrawal request?">
                    Approve
                </flux:button>

                <div class="border-t dark:border-zinc-700 pt-3">
                    <flux:field>
                        <flux:label>Rejection Reason <flux:required /></flux:label>
                        <flux:textarea wire:model="notes" rows="2" placeholder="Enter reason for rejection…" />
                        <flux:error name="notes" />
                    </flux:field>
                    <flux:button wire:click="reject" variant="danger" class="w-full mt-3" wire:confirm="Reject this withdrawal?">
                        Reject
                    </flux:button>
                </div>
            </div>
        </flux:card>
    @else
        <flux:card>
            <flux:heading class="mb-3">Decision Record</flux:heading>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-zinc-500">Processed By</dt><dd>{{ $withdrawal->processedBy?->name ?? '—' }}</dd></div>
                @if ($withdrawal->notes)
                    <div><dt class="text-zinc-500">Notes</dt><dd>{{ $withdrawal->notes }}</dd></div>
                @endif
            </dl>
        </flux:card>
    @endif
</div>

<div>
<div class="mb-6 flex items-center gap-2">
    <flux:button href="{{ route('withdrawals.index') }}" icon="arrow-left" size="sm" variant="ghost" wire:navigate />
    <flux:heading size="xl">Request Withdrawal</flux:heading>
</div>

<flux:card class="max-w-xl">
    @if ($errors->has('quantityKg') || $errors->has('selectedBarIds'))
        <flux:callout variant="danger" class="mb-4">
            {{ $errors->first('quantityKg') ?? $errors->first('selectedBarIds') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-5">
        <flux:field>
            <flux:label>Deposit <flux:required /></flux:label>
            <flux:select wire:model.live="depositId" placeholder="Select a deposit…">
                @foreach ($deposits as $dep)
                    <flux:select.option value="{{ $dep->id }}">
                        {{ $dep->deposit_number }} — {{ ucfirst($dep->metal_type->value) }} ({{ ucfirst($dep->storage_type->value) }})
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="depositId" />
        </flux:field>

        @if ($selectedDeposit)
            @if ($selectedDeposit->storage_type->value === 'unallocated')
                <flux:field>
                    <flux:label>Quantity (kg) <flux:required /></flux:label>
                    <flux:input type="number" step="0.000001" min="0.000001" wire:model="quantityKg" placeholder="e.g. 1.000000" />
                    <flux:description>Available: {{ number_format($selectedDeposit->availableQuantityKg(), 6) }} kg</flux:description>
                    <flux:error name="quantityKg" />
                </flux:field>
            @else
                <div>
                    <flux:label class="mb-2 block">Select Bars <flux:required /></flux:label>
                    @foreach ($selectedDeposit->bars()->where('status', 'held')->get() as $bar)
                        <label class="flex items-center gap-3 py-2 border-b dark:border-zinc-700 last:border-0 cursor-pointer">
                            <input type="checkbox" wire:model="selectedBarIds" value="{{ $bar->id }}" class="rounded" />
                            <span class="font-mono text-sm">{{ $bar->serial_number }}</span>
                            <span class="text-zinc-500 text-xs">{{ number_format($bar->weight_kg, 6) }} kg</span>
                        </label>
                    @endforeach
                    <flux:error name="selectedBarIds" />
                </div>
            @endif
        @endif

        <flux:field>
            <flux:label>Notes</flux:label>
            <flux:textarea wire:model="notes" rows="2" />
        </flux:field>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">Submit Request</flux:button>
            <flux:button href="{{ route('withdrawals.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
        </div>
    </form>
</flux:card>
</div>

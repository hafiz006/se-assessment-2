<div>
<div class="mb-6 flex items-center gap-2">
    <flux:button href="{{ route('deposits.index') }}" icon="arrow-left" size="sm" variant="ghost" wire:navigate />
    <flux:heading size="xl">New Deposit</flux:heading>
</div>

<flux:card class="max-w-2xl">
    @if ($errors->any())
        <flux:callout variant="danger" class="mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-5">
        <flux:field>
            <flux:label>Metal Type <flux:required /></flux:label>
            <flux:select wire:model="metalType" placeholder="Select metal…">
                @foreach ($metalTypes as $metal)
                    <flux:select.option value="{{ $metal->value }}">{{ ucfirst($metal->value) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="metalType" />
        </flux:field>

        <flux:field>
            <flux:label>Storage Type <flux:required /></flux:label>
            <flux:select wire:model.live="storageType" placeholder="Select storage…">
                <flux:select.option value="unallocated">Unallocated</flux:select.option>
                @unless ($isRetail)
                    <flux:select.option value="allocated">Allocated</flux:select.option>
                @endunless
            </flux:select>
            <flux:error name="storageType" />
            @if ($isRetail)
                <flux:description>Retail accounts can only hold unallocated storage.</flux:description>
            @endif
        </flux:field>

        <flux:field>
            <flux:label>Total Quantity (kg) <flux:required /></flux:label>
            <flux:input type="number" step="0.000001" min="0.000001" wire:model="quantityKg" placeholder="e.g. 5.000000" />
            <flux:error name="quantityKg" />
        </flux:field>

        <flux:field>
            <flux:label>Notes</flux:label>
            <flux:textarea wire:model="notes" rows="2" />
        </flux:field>

        @if ($storageType === 'allocated')
            <div class="border-t dark:border-zinc-700 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <flux:heading size="sm">Bar Details</flux:heading>
                    <flux:button type="button" wire:click="addBar" size="sm" icon="plus" variant="ghost">Add Bar</flux:button>
                </div>
                <flux:error name="bars" />
                @foreach ($bars as $i => $bar)
                    <div class="flex gap-3 items-end mb-3">
                        <flux:field class="flex-1">
                            @if ($i === 0)<flux:label>Serial Number</flux:label>@endif
                            <flux:input wire:model="bars.{{ $i }}.serial_number" placeholder="e.g. GOLD-BAR-00001" />
                            <flux:error name="bars.{{ $i }}.serial_number" />
                        </flux:field>
                        <flux:field class="w-40">
                            @if ($i === 0)<flux:label>Weight (kg)</flux:label>@endif
                            <flux:input type="number" step="0.000001" min="0.000001" wire:model="bars.{{ $i }}.weight_kg" placeholder="12.441" />
                            <flux:error name="bars.{{ $i }}.weight_kg" />
                        </flux:field>
                        @if (count($bars) > 1)
                            <flux:button type="button" wire:click="removeBar({{ $i }})" size="sm" variant="ghost" icon="trash" class="mb-0.5 text-red-500" />
                        @endif
                    </div>
                @endforeach
                @php $barTotal = array_sum(array_column($bars, 'weight_kg')); @endphp
                <flux:text class="text-xs mt-1 @if(abs($barTotal - (float)$quantityKg) < 0.000001 && $quantityKg) text-green-600 @else text-zinc-500 @endif">
                    Bar total: {{ number_format($barTotal, 6) }} kg
                    @if ($quantityKg) / {{ number_format((float)$quantityKg, 6) }} kg required @endif
                </flux:text>
            </div>
        @endif

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">Submit Deposit</flux:button>
            <flux:button href="{{ route('deposits.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
        </div>
    </form>
</flux:card>
</div>

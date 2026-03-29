<div class="mb-6 flex items-center gap-2">
    <flux:button href="{{ route('admin.deposits.index') }}" icon="arrow-left" size="sm" variant="ghost" wire:navigate />
    <flux:heading size="xl">Create Deposit</flux:heading>
</div>

<flux:card class="max-w-2xl">
    @if ($errors->any())
        <flux:callout variant="danger" class="mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-5">
        <flux:field>
            <flux:label>Account <flux:required /></flux:label>
            <flux:select wire:model.live="accountId" placeholder="Select account…">
                @foreach ($accounts as $account)
                    <flux:option value="{{ $account->id }}">
                        {{ $account->account_number }} — {{ $account->user->name }} ({{ ucfirst($account->client_type->value) }})
                    </flux:option>
                @endforeach
            </flux:select>
            <flux:error name="accountId" />
        </flux:field>

        <flux:field>
            <flux:label>Metal Type <flux:required /></flux:label>
            <flux:select wire:model="metalType" placeholder="Select metal…">
                @foreach ($metalTypes as $metal)
                    <flux:option value="{{ $metal->value }}">{{ ucfirst($metal->value) }}</flux:option>
                @endforeach
            </flux:select>
            <flux:error name="metalType" />
        </flux:field>

        <flux:field>
            <flux:label>Storage Type <flux:required /></flux:label>
            <flux:select wire:model.live="storageType" placeholder="Select storage…">
                <flux:option value="unallocated">Unallocated</flux:option>
                @unless ($isRetail)
                    <flux:option value="allocated">Allocated</flux:option>
                @endunless
            </flux:select>
            <flux:error name="storageType" />
        </flux:field>

        <flux:field>
            <flux:label>Total Quantity (kg) <flux:required /></flux:label>
            <flux:input type="number" step="0.000001" min="0.000001" wire:model="quantityKg" />
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
                        </flux:field>
                        <flux:field class="w-40">
                            @if ($i === 0)<flux:label>Weight (kg)</flux:label>@endif
                            <flux:input type="number" step="0.000001" wire:model="bars.{{ $i }}.weight_kg" />
                        </flux:field>
                        @if (count($bars) > 1)
                            <flux:button type="button" wire:click="removeBar({{ $i }})" size="sm" variant="ghost" icon="trash" class="text-red-500" />
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">Create Deposit</flux:button>
            <flux:button href="{{ route('admin.deposits.index') }}" variant="ghost" wire:navigate>Cancel</flux:button>
        </div>
    </form>
</flux:card>

<div class="flex items-center justify-between mb-6">
    <flux:heading size="xl">All Deposits</flux:heading>
    <flux:button href="{{ route('admin.deposits.create') }}" variant="primary" icon="plus" wire:navigate>New Deposit</flux:button>
</div>

<flux:card>
    <div class="flex flex-wrap gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search deposit number…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:select wire:model.live="metalFilter" class="w-36">
            <flux:option value="">All Metals</flux:option>
            @foreach (\App\Enums\MetalType::cases() as $m)
                <flux:option value="{{ $m->value }}">{{ ucfirst($m->value) }}</flux:option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="typeFilter" class="w-40">
            <flux:option value="">All Types</flux:option>
            <flux:option value="allocated">Allocated</flux:option>
            <flux:option value="unallocated">Unallocated</flux:option>
        </flux:select>
        <flux:select wire:model.live="statusFilter" class="w-36">
            <flux:option value="">All Statuses</flux:option>
            @foreach (\App\Enums\DepositStatus::cases() as $s)
                <flux:option value="{{ $s->value }}">{{ ucfirst($s->value) }}</flux:option>
            @endforeach
        </flux:select>
    </div>

    <flux:table>
        <flux:columns>
            <flux:column>Number</flux:column>
            <flux:column>Account</flux:column>
            <flux:column>Metal</flux:column>
            <flux:column>Type</flux:column>
            <flux:column>Quantity (kg)</flux:column>
            <flux:column>Status</flux:column>
            <flux:column>Date</flux:column>
            <flux:column></flux:column>
        </flux:columns>
        <flux:rows>
            @forelse ($deposits as $deposit)
                <flux:row :key="$deposit->id">
                    <flux:cell class="font-mono text-sm">{{ $deposit->deposit_number }}</flux:cell>
                    <flux:cell class="text-sm">{{ $deposit->account->account_number }}</flux:cell>
                    <flux:cell class="capitalize">{{ $deposit->metal_type->value }}</flux:cell>
                    <flux:cell class="capitalize">{{ $deposit->storage_type->value }}</flux:cell>
                    <flux:cell>{{ number_format($deposit->quantity_kg, 6) }}</flux:cell>
                    <flux:cell><x-deposit-status-badge :status="$deposit->status" /></flux:cell>
                    <flux:cell class="text-xs text-zinc-500">{{ $deposit->created_at->format('d M Y') }}</flux:cell>
                    <flux:cell>
                        <flux:button size="xs" href="{{ route('admin.deposits.show', $deposit) }}" wire:navigate>View</flux:button>
                    </flux:cell>
                </flux:row>
            @empty
                <flux:row>
                    <flux:cell colspan="8" class="text-center py-8 text-zinc-500">No deposits found.</flux:cell>
                </flux:row>
            @endforelse
        </flux:rows>
    </flux:table>
    <div class="mt-4">{{ $deposits->links() }}</div>
</flux:card>

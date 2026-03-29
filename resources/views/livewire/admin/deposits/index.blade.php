<div>
<div class="flex items-center justify-between mb-6">
    <flux:heading size="xl">All Deposits</flux:heading>
    <flux:button href="{{ route('admin.deposits.create') }}" variant="primary" icon="plus" wire:navigate>New Deposit</flux:button>
</div>

<flux:card>
    <div class="flex flex-wrap gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search deposit number…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:select wire:model.live="metalFilter" class="w-36">
            <flux:select.option value="">All Metals</flux:select.option>
            @foreach (\App\Enums\MetalType::cases() as $m)
                <flux:select.option value="{{ $m->value }}">{{ ucfirst($m->value) }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="typeFilter" class="w-40">
            <flux:select.option value="">All Types</flux:select.option>
            <flux:select.option value="allocated">Allocated</flux:select.option>
            <flux:select.option value="unallocated">Unallocated</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="statusFilter" class="w-36">
            <flux:select.option value="">All Statuses</flux:select.option>
            @foreach (\App\Enums\DepositStatus::cases() as $s)
                <flux:select.option value="{{ $s->value }}">{{ ucfirst($s->value) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Number</flux:table.column>
            <flux:table.column>Account</flux:table.column>
            <flux:table.column>Metal</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Quantity (kg)</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($deposits as $deposit)
                <flux:table.row :key="$deposit->id">
                    <flux:table.cell class="font-mono text-sm">{{ $deposit->deposit_number }}</flux:table.cell>
                    <flux:table.cell class="text-sm">{{ $deposit->account->account_number }}</flux:table.cell>
                    <flux:table.cell class="capitalize">{{ $deposit->metal_type->value }}</flux:table.cell>
                    <flux:table.cell class="capitalize">{{ $deposit->storage_type->value }}</flux:table.cell>
                    <flux:table.cell>{{ number_format($deposit->quantity_kg, 6) }}</flux:table.cell>
                    <flux:table.cell><x-deposit-status-badge :status="$deposit->status" /></flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-500">{{ $deposit->created_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="xs" href="{{ route('admin.deposits.show', $deposit) }}" wire:navigate>View</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-8 text-zinc-500">No deposits found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    <div class="mt-4">{{ $deposits->links() }}</div>
</flux:card>
</div>

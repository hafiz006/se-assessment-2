<div>
<div class="flex items-center justify-between mb-6">
    <flux:heading size="xl">Accounts</flux:heading>
</div>

<flux:card>
    <div class="flex gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search name, email, account number…" icon="magnifying-glass" class="flex-1" />
        <flux:select wire:model.live="statusFilter" class="w-36">
            <flux:select.option value="">All Statuses</flux:select.option>
            @foreach (\App\Enums\AccountStatus::cases() as $s)
                <flux:select.option value="{{ $s->value }}">{{ ucfirst($s->value) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Account #</flux:table.column>
            <flux:table.column>Client</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Created</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($accounts as $account)
                <flux:table.row :key="$account->id">
                    <flux:table.cell class="font-mono text-sm">{{ $account->account_number }}</flux:table.cell>
                    <flux:table.cell>
                        <p class="font-medium">{{ $account->user->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $account->user->email }}</p>
                    </flux:table.cell>
                    <flux:table.cell class="capitalize">{{ $account->client_type->value }}</flux:table.cell>
                    <flux:table.cell><x-account-status-badge :status="$account->status" /></flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-500">{{ $account->created_at->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="xs" href="{{ route('admin.accounts.show', $account) }}" wire:navigate>View</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-8 text-zinc-500">No accounts found.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    <div class="mt-4">{{ $accounts->links() }}</div>
</flux:card>
</div>

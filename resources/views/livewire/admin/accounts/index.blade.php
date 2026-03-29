<div class="flex items-center justify-between mb-6">
    <flux:heading size="xl">Accounts</flux:heading>
</div>

<flux:card>
    <div class="flex gap-3 mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search name, email, account number…" icon="magnifying-glass" class="flex-1" />
        <flux:select wire:model.live="statusFilter" class="w-36">
            <flux:option value="">All Statuses</flux:option>
            @foreach (\App\Enums\AccountStatus::cases() as $s)
                <flux:option value="{{ $s->value }}">{{ ucfirst($s->value) }}</flux:option>
            @endforeach
        </flux:select>
    </div>

    <flux:table>
        <flux:columns>
            <flux:column>Account #</flux:column>
            <flux:column>Client</flux:column>
            <flux:column>Type</flux:column>
            <flux:column>Status</flux:column>
            <flux:column>Created</flux:column>
            <flux:column></flux:column>
        </flux:columns>
        <flux:rows>
            @forelse ($accounts as $account)
                <flux:row :key="$account->id">
                    <flux:cell class="font-mono text-sm">{{ $account->account_number }}</flux:cell>
                    <flux:cell>
                        <p class="font-medium">{{ $account->user->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $account->user->email }}</p>
                    </flux:cell>
                    <flux:cell class="capitalize">{{ $account->client_type->value }}</flux:cell>
                    <flux:cell><x-account-status-badge :status="$account->status" /></flux:cell>
                    <flux:cell class="text-xs text-zinc-500">{{ $account->created_at->format('d M Y') }}</flux:cell>
                    <flux:cell>
                        <flux:button size="xs" href="{{ route('admin.accounts.show', $account) }}" wire:navigate>View</flux:button>
                    </flux:cell>
                </flux:row>
            @empty
                <flux:row>
                    <flux:cell colspan="6" class="text-center py-8 text-zinc-500">No accounts found.</flux:cell>
                </flux:row>
            @endforelse
        </flux:rows>
    </flux:table>
    <div class="mt-4">{{ $accounts->links() }}</div>
</flux:card>

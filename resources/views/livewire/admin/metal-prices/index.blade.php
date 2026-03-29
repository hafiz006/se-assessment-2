<flux:heading size="xl" class="mb-6">Metal Spot Prices</flux:heading>

@if (session('status'))
    <flux:callout variant="success" class="mb-4">{{ session('status') }}</flux:callout>
@endif

{{-- Current prices + inline edit --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    @foreach (\App\Enums\MetalType::cases() as $metal)
        @php $form = $priceForm[$metal->value]; @endphp
        <flux:card>
            <flux:heading class="capitalize mb-3">{{ $metal->value }}</flux:heading>
            @if ($form['editing'])
                <div class="space-y-3">
                    <flux:field>
                        <flux:label>Price ($/kg)</flux:label>
                        <flux:input type="number" step="0.0001" min="0.01" wire:model="priceForm.{{ $metal->value }}.price" />
                        <flux:error name="priceForm.{{ $metal->value }}.price" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Effective Date</flux:label>
                        <flux:input type="date" wire:model="priceForm.{{ $metal->value }}.date" />
                        <flux:error name="priceForm.{{ $metal->value }}.date" />
                    </flux:field>
                    <div class="flex gap-2">
                        <flux:button wire:click="savePrice('{{ $metal->value }}')" variant="primary" size="sm">Save</flux:button>
                        <flux:button wire:click="$set('priceForm.{{ $metal->value }}.editing', false)" variant="ghost" size="sm">Cancel</flux:button>
                    </div>
                </div>
            @else
                <p class="text-2xl font-bold mt-1">${{ $form['price'] ? number_format((float)$form['price'], 2) : '—' }}</p>
                <flux:text class="text-xs text-zinc-500 mb-3">per kg</flux:text>
                <flux:button wire:click="editMetal('{{ $metal->value }}')" size="sm" variant="ghost" icon="pencil">Update Price</flux:button>
            @endif
        </flux:card>
    @endforeach
</div>

{{-- Price history --}}
<flux:card>
    <flux:heading class="mb-4">Recent Price History</flux:heading>
    <flux:table>
        <flux:columns>
            <flux:column>Metal</flux:column>
            <flux:column>Price ($/kg)</flux:column>
            <flux:column>Effective Date</flux:column>
        </flux:columns>
        <flux:rows>
            @forelse ($recentPrices as $price)
                <flux:row :key="$price->id">
                    <flux:cell class="capitalize">{{ $price->metal_type->value }}</flux:cell>
                    <flux:cell>${{ number_format($price->price_per_kg, 4) }}</flux:cell>
                    <flux:cell>{{ $price->effective_date->format('d M Y') }}</flux:cell>
                </flux:row>
            @empty
                <flux:row><flux:cell colspan="3" class="text-center py-4 text-zinc-500">No price history.</flux:cell></flux:row>
            @endforelse
        </flux:rows>
    </flux:table>
</flux:card>

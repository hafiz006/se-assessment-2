<?php

namespace App\Livewire\Admin\MetalPrices;

use App\Enums\MetalType;
use App\Models\MetalPrice;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Metal Prices')]
class Index extends Component
{
    /** @var array<string, array{price: string, date: string, editing: bool}> */
    public array $priceForm = [];

    public function mount(): void
    {
        foreach (MetalType::cases() as $metal) {
            $latest = MetalPrice::where('metal_type', $metal)
                ->orderByDesc('effective_date')
                ->first();

            $this->priceForm[$metal->value] = [
                'price'   => $latest?->price_per_kg ?? '',
                'date'    => today()->toDateString(),
                'editing' => false,
            ];
        }
    }

    public function editMetal(string $metal): void
    {
        $this->priceForm[$metal]['editing'] = true;
    }

    public function savePrice(string $metal): void
    {
        $this->validate([
            "priceForm.{$metal}.price" => 'required|numeric|min:0.01',
            "priceForm.{$metal}.date"  => 'required|date',
        ]);

        MetalPrice::updateOrCreate(
            ['metal_type' => $metal, 'effective_date' => $this->priceForm[$metal]['date']],
            ['price_per_kg' => $this->priceForm[$metal]['price']],
        );

        $this->priceForm[$metal]['editing'] = false;
        session()->flash('status', 'Price updated for ' . ucfirst($metal) . '.');
    }

    public function render(): View
    {
        $recentPrices = MetalPrice::orderByDesc('effective_date')->orderBy('metal_type')->take(30)->get();

        return view('livewire.admin.metal-prices.index', compact('recentPrices'));
    }
}

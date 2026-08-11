<?php

namespace App\Livewire\Site;

use Livewire\Component;

class QuoteCartSummary extends Component
{
    public int $count = 0;

    protected $listeners = [
        'cart-updated'        => 'onCartUpdated',
        'quote-cart:updated'  => 'onCartUpdated',
    ];

    public function mount(): void
    {
        $this->count = QuoteCart::count();
    }

    public function onCartUpdated(?int $count = null): void
    {
        $this->count = $count ?? QuoteCart::count();
    }

    public function render()
    {
        return view('livewire.site.quote-cart-summary');
    }
}

<?php

namespace App\Livewire\Site;

use Livewire\Component;

class QuoteCartFloat extends Component
{
    public int $count = 0;
    public int $progress = 0;
    public bool $meetsMin = false;
    public ?string $stateUf = null;

    protected $listeners = [
        'cart-updated'        => 'onCartUpdated',
        'quote-cart:updated'  => 'onCartUpdated',
    ];

    public function mount(): void
    {
        $this->hydrateFromCart();
    }

    protected function hydrateFromCart(): void
    {
        $this->count    = QuoteCart::count();
        $this->progress = QuoteCart::progressPercent();
        $this->meetsMin = QuoteCart::meetsMinimum();
        $this->stateUf  = QuoteCart::stateUf();
    }

    /** Aceita parâmetro opcional — o dispatch envia `count:`. */
    public function onCartUpdated(?int $count = null): void
    {
        $this->hydrateFromCart();
    }

    public function render()
    {
        return view('livewire.site.quote-cart-float');
    }
}

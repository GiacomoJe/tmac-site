<?php

namespace App\Livewire\Site;

use App\Models\State;
use Livewire\Attributes\Computed;
use Livewire\Component;

class QuoteCartButton extends Component
{
    public int $count = 0;
    public int $progress = 0;
    public bool $meetsMin = false;
    public ?string $stateUf = null;

    /** Listeners legados — funcionam em qualquer versão do Livewire 3. */
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

    #[Computed]
    public function state(): ?State
    {
        return $this->stateUf ? State::where('uf', $this->stateUf)->first() : null;
    }

    public function render()
    {
        return view('livewire.site.quote-cart-button');
    }
}

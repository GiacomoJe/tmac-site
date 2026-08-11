<?php

namespace App\Livewire\Site;

use App\Models\Product;
use App\Models\State;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Modal de seleção de estado (UF) antes da cotação.
 *
 * Eventos escutados:
 *   - quote-cart:require-state  → abre o modal (com optional product_id pendente)
 *   - quote-cart:open-state     → abre o modal explicitamente (trocar UF)
 *
 * Eventos disparados:
 *   - quote-cart:updated   → para outros componentes (Button, Float) recarregarem
 *   - quote-cart:added     → quando o produto pendente foi adicionado após escolher UF
 */
class StateSelectorModal extends Component
{
    public bool $open = false;

    public ?string $uf = null;

    /** ID do produto que ficou pendente esperando UF (opcional) */
    public ?int $pendingProductId = null;
    public int  $pendingQty = 1;

    public function mount(): void
    {
        $this->uf = QuoteCart::stateUf();
    }

    #[On('quote-cart:require-state')]
    public function openForProduct(?int $productId = null, int $qty = 1): void
    {
        // Se já tem UF, adiciona direto e dispara o evento de atualização
        if (QuoteCart::hasState() && $productId) {
            QuoteCart::add($productId, $qty);
            $this->dispatch('quote-cart:updated');
            $this->dispatch('quote-cart:added', productId: $productId);
            return;
        }

        $this->pendingProductId = $productId;
        $this->pendingQty = max(1, $qty);
        $this->open = true;
    }

    #[On('quote-cart:open-state')]
    public function openForChange(): void
    {
        $this->pendingProductId = null;
        $this->uf = QuoteCart::stateUf();
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
        $this->pendingProductId = null;
    }

    public function confirm(): void
    {
        $this->validate([
            'uf' => 'required|string|size:2|exists:states,uf',
        ]);

        QuoteCart::setStateUf($this->uf);

        // Se havia um produto pendente quando abriu o modal, adiciona agora
        if ($this->pendingProductId) {
            QuoteCart::add($this->pendingProductId, $this->pendingQty);
            $this->dispatch('quote-cart:added', productId: $this->pendingProductId);
        }

        $this->dispatch('quote-cart:updated');
        $this->close();
    }

    public function getStatesProperty()
    {
        return State::orderBy('name')->get(['id', 'uf', 'name', 'region', 'min_quote_value']);
    }

    public function getSelectedStateProperty(): ?State
    {
        return $this->uf ? State::where('uf', $this->uf)->first() : null;
    }

    public function render()
    {
        return view('livewire.site.state-selector-modal');
    }
}

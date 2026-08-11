<?php

namespace App\Livewire\Site;

use App\Mail\QuoteRequestSubmitted;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestItem;
use App\Models\State;
use App\Services\QuoteRouter;
use App\Services\RdStation;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class QuoteForm extends Component
{
    public int $step = 1; // 1=Itens, 2=Dados, 3=Confirmação

    // Step 2 - dados
    #[Validate('required|string|max:150')]
    public string $company = '';

    #[Validate('required|string|min:14|max:20')]
    public string $cnpj = '';

    #[Validate('required|string|max:50')]
    public string $segment = 'loja';

    #[Validate('required|string|max:150')]
    public string $customer_name = '';

    #[Validate('required|email|max:150')]
    public string $email = '';

    #[Validate('required|string|max:30')]
    public string $phone = '';

    #[Validate('required|exists:states,id')]
    public ?int $state_id = null;

    #[Validate('required|string|max:120')]
    public string $city = '';

    #[Validate('nullable|string|max:2000')]
    public string $message = '';

    #[Validate('accepted')]
    public bool $consent = false;

    public ?QuoteRequest $submitted = null;

    public function mount(): void
    {
        $this->step = 1;
        $this->hydrateFromCartState();
    }

    /** Pré-popula state_id a partir do UF salvo na sessão (StateSelectorModal). */
    protected function hydrateFromCartState(): void
    {
        if ($uf = QuoteCart::stateUf()) {
            $this->state_id = State::where('uf', $uf)->value('id');
        }
    }

    /** Refresca state_id quando o usuário trocar UF via modal enquanto está nesta página. */
    #[On('quote-cart:updated')]
    public function onCartUpdated(): void
    {
        $this->hydrateFromCartState();
    }

    public function goToStep(int $step): void
    {
        if ($step === 2 && QuoteCart::count() === 0) {
            $this->addError('cart', 'Adicione pelo menos um produto antes de continuar.');
            return;
        }
        // Não permite avançar se não atingiu o mínimo do estado
        if ($step === 2 && ! QuoteCart::meetsMinimum()) {
            $this->addError('cart', 'Você ainda não atingiu o valor mínimo de cotação do seu estado. Adicione mais produtos para liberar.');
            return;
        }
        $this->step = $step;
    }

    public function increment(int $productId): void
    {
        $items = QuoteCart::items();
        $current = $items[$productId]['quantity'] ?? 0;
        QuoteCart::update($productId, $current + 1);
        $this->broadcastCart();
    }

    public function decrement(int $productId): void
    {
        $items = QuoteCart::items();
        $current = $items[$productId]['quantity'] ?? 1;
        QuoteCart::update($productId, max(1, $current - 1));
        $this->broadcastCart();
    }

    /** Atualiza quantidade digitada diretamente (clamp 1..9999). */
    public function setQuantity(int $productId, $quantity): void
    {
        $qty = (int) $quantity;
        if ($qty < 1)    $qty = 1;
        if ($qty > 9999) $qty = 9999;

        QuoteCart::update($productId, $qty);
        $this->broadcastCart();
    }

    public function remove(int $productId): void
    {
        QuoteCart::remove($productId);
        $this->broadcastCart();
    }

    /** Estado atual do carrinho (usado no retorno dos métodos e nos eventos). */
    protected function cartPayload(): array
    {
        return [
            'count'     => QuoteCart::count(),
            'distinct'  => QuoteCart::distinctCount(),
            'progress'  => QuoteCart::progressPercent(),
            'meets_min' => QuoteCart::meetsMinimum(),
            'state_uf'  => QuoteCart::stateUf(),
        ];
    }

    /**
     * Notifica todos os componentes Livewire E o JS do layout,
     * para que badges, barras e cores atualizem imediatamente.
     */
    protected function broadcastCart(): void
    {
        $payload = $this->cartPayload();

        // Componentes Livewire (header, float, summary)
        $this->dispatch('cart-updated', count: $payload['count']);
        $this->dispatch('quote-cart:updated');

        // JS do layout — atualiza cores/badges na hora (window.TMAC.updateCartUI)
        $this->dispatch('cart-ui-sync', payload: $payload);
    }

    public function submit(QuoteRouter $router, RdStation $rdStation): void
    {
        $items = QuoteCart::items();
        if (empty($items)) {
            $this->addError('cart', 'Adicione pelo menos um produto à cotação antes de enviar.');
            $this->step = 1;
            return;
        }

        // Trava server-side: exige mínimo do estado atingido
        if (! QuoteCart::meetsMinimum()) {
            $this->addError('cart', 'O valor mínimo de cotação do seu estado ainda não foi atingido.');
            $this->step = 1;
            return;
        }

        $this->validate();

        $request = QuoteRequest::create([
            'customer_name' => $this->customer_name,
            'company' => $this->company,
            'cnpj' => $this->cnpj,
            'segment' => $this->segment,
            'email' => $this->email,
            'phone' => $this->phone,
            'state_id' => $this->state_id,
            'city' => $this->city,
            'message' => $this->message ?: null,
            'source' => session('utm.source'),
            'utm_payload' => session('utm', []),
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);

        foreach (QuoteCart::products() as $product) {
            $data = $items[$product->id];
            QuoteRequestItem::create([
                'quote_request_id' => $request->id,
                'product_id' => $product->id,
                'product_name_snapshot' => $product->name,
                'product_sku_snapshot' => $product->sku,
                'quantity' => $data['quantity'] ?? 1,
                'notes' => $data['notes'] ?? null,
            ]);
        }

        $representative = $router->assign($request);

        try {
            Mail::to(config('quote.commercial_email'))
                ->send(new QuoteRequestSubmitted($request->fresh('items', 'state', 'representative')));

            if ($representative && $representative->email) {
                Mail::to($representative->email)
                    ->send(new QuoteRequestSubmitted($request->fresh('items', 'state', 'representative')));
            }
        } catch (\Throwable $e) {
            logger()->warning('Quote email failed: '.$e->getMessage());
        }

        $rdStation->trackQuoteSubmit($request->load('state'));

        QuoteCart::clear();
        $this->submitted = $request->load('representative.states', 'state');
        $this->step = 3;
        $this->dispatch('cart-updated', count: 0);
        $this->dispatch('quote-submitted');
    }

    public function render()
    {
        return view('livewire.site.quote-form', [
            'states'      => State::orderBy('name')->get(),
            'products'    => QuoteCart::products(),
            'cartItems'   => QuoteCart::items(),
            'cartState'   => QuoteCart::state(),
            'progress'    => QuoteCart::progressPercent(),
            'meetsMin'    => QuoteCart::meetsMinimum(),
        ]);
    }
}

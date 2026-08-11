<div>
    {{-- Step indicator --}}
    <div class="steps mt-2">
        <div class="step {{ $step >= 1 ? 'step--done' : '' }} {{ $step === 1 ? 'step--current' : '' }}"></div>
        <div class="step {{ $step >= 2 ? 'step--done' : '' }} {{ $step === 2 ? 'step--current' : '' }}"></div>
        <div class="step {{ $step >= 3 ? 'step--done' : '' }} {{ $step === 3 ? 'step--current' : '' }}"></div>
    </div>
    <div class="mt-2 font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint">
        Passo {{ $step }}/3 · {{ ['Itens','Seus dados','Confirmação'][$step-1] }}
    </div>

    @error('cart') <div class="bg-accent-soft text-accent p-3 rounded mt-3 text-sm">{{ $message }}</div> @enderror

    {{-- ═══ STEP 1: Itens ═══ --}}
    @if($step === 1)
        {{-- Barra de progresso (UF + % atingido) --}}
        @if($cartState || $products->isNotEmpty())
            <div class="mt-4 p-4 rounded-xl bg-bg-elev border border-line">
                <x-site.quote-progress
                    :percent="$progress"
                    :state="$cartState"
                    :meets-min="$meetsMin"
                    :show-change-state="true"
                />
                @if(!$cartState)
                    <button type="button"
                            onclick="if(window.Livewire) window.Livewire.dispatch('quote-cart:open-state');"
                            class="mt-3 btn-signal btn-block h-11 font-bold uppercase tracking-[0.04em] text-[13px]">
                        Selecionar estado para começar
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </button>
                @endif
            </div>
        @endif

        <div class="mt-5">
            @if($products->isEmpty())
                <div class="p-6 border border-dashed border-line rounded-xl bg-bg-elev text-center">
                    <div class="font-display font-bold text-lg text-ink">Carrinho de cotação vazio</div>
                    <p class="text-sm text-ink-soft mt-2">
                        @if($cartState)
                            Adicione produtos do catálogo para preencher seu carrinho de cotação.
                        @else
                            Selecione seu estado e adicione produtos do catálogo para começar.
                        @endif
                    </p>
                    <div class="flex gap-2 mt-4 justify-center">
                        <a href="{{ route('site.products') }}" class="btn-primary">Buscar produtos</a>
                    </div>
                </div>
            @else
                @foreach($products as $p)
                    <div class="flex gap-3 py-3 border-b border-line" wire:key="row-{{ $p->id }}">
                        <div class="w-16 h-16 flex-shrink-0 rounded border border-line"
                             style="background-image:repeating-linear-gradient(135deg,#ECEAE3,#ECEAE3 4px,#E4E2DB 4px,#E4E2DB 8px)">
                            @if($p->main_image)
                                <img src="{{ asset('storage/'.$p->main_image) }}" alt="" class="w-full h-full object-contain p-1">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            @if($p->brand)<div class="font-mono text-[10px] uppercase tracking-[0.06em] text-ink-faint">{{ $p->brand->name }}</div>@endif
                            <div class="text-[13px] font-medium leading-tight mt-0.5">{{ $p->name }}</div>
                            <div class="font-mono text-[11px] text-ink-faint mt-0.5">{{ $p->sku }}</div>
                            @php $qtyAtual = $cartItems[$p->id]['quantity'] ?? 1; @endphp
                            <div class="inline-flex items-center bg-bg-sunken border border-line rounded h-8 mt-1.5 focus-within:border-signal focus-within:ring-2 focus-within:ring-signal/20 transition">
                                <button type="button"
                                        wire:click="decrement({{ $p->id }})"
                                        aria-label="Diminuir quantidade"
                                        class="w-8 h-8 flex items-center justify-center text-ink hover:bg-line/40 transition select-none">−</button>

                                {{-- wire:key inclui o valor: quando a quantidade muda no servidor,
                                     o Livewire recria o input já com o número novo. --}}
                                <input
                                    wire:key="qty-{{ $p->id }}-{{ $qtyAtual }}"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="4"
                                    value="{{ $qtyAtual }}"
                                    wire:change="setQuantity({{ $p->id }}, $event.target.value)"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                    onfocus="this.select()"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}"
                                    aria-label="Quantidade"
                                    class="w-14 h-full text-center bg-transparent border-x border-line font-mono text-[12px] tabular-nums focus:outline-none focus:bg-white"
                                />

                                <button type="button"
                                        wire:click="increment({{ $p->id }})"
                                        aria-label="Aumentar quantidade"
                                        class="w-8 h-8 flex items-center justify-center text-ink hover:bg-line/40 transition select-none">+</button>
                            </div>
                        </div>
                        <button wire:click="remove({{ $p->id }})" class="font-mono text-[10px] uppercase tracking-[0.06em] text-ink-faint hover:text-accent self-start">
                            Remover
                        </button>
                    </div>
                @endforeach

                <a href="{{ route('site.products') }}" class="btn-ghost btn-block mt-4">+ Adicionar mais itens</a>

                @if($meetsMin)
                    <button wire:click="goToStep(2)" class="btn-primary btn-block mt-2">
                        Continuar
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                    </button>
                @else
                    <div class="mt-2">
                        <button type="button" disabled
                                class="btn-primary btn-block opacity-50 cursor-not-allowed pointer-events-none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            Faltam {{ 100 - $progress }}% para liberar a cotação
                        </button>
                        <p class="mt-2 text-[12px] text-ink-faint text-center">
                            Adicione mais produtos para atingir o valor mínimo de cotação do seu estado.
                        </p>
                    </div>
                @endif
            @endif
        </div>
    @endif

    {{-- ═══ STEP 2: Dados ═══ --}}
    @if($step === 2)
        <form wire:submit="submit" class="mt-5 space-y-3">
            <label class="field">
                <span class="field-label">Razão social *</span>
                <input wire:model="company" class="field-input" placeholder="Sua empresa" required>
                @error('company')<span class="field-hint text-accent">{{ $message }}</span>@enderror
            </label>

            <label class="field">
                <span class="field-label">CNPJ *</span>
                <input wire:model="cnpj" class="field-input" placeholder="00.000.000/0001-00" required>
                <span class="field-hint">Obrigatório · atendemos somente atacado</span>
                @error('cnpj')<span class="field-hint text-accent">{{ $message }}</span>@enderror
            </label>

            <label class="field">
                <span class="field-label">Tipo de negócio *</span>
                <select wire:model="segment" class="field-select" required>
                    @foreach(\App\Models\QuoteRequest::SEGMENTS as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </label>

            <label class="field">
                <span class="field-label">Nome do comprador *</span>
                <input wire:model="customer_name" class="field-input" placeholder="Ex: João Silva" required>
                @error('customer_name')<span class="field-hint text-accent">{{ $message }}</span>@enderror
            </label>

            <label class="field">
                <span class="field-label">E-mail *</span>
                <input wire:model="email" type="email" class="field-input" placeholder="voce@empresa.com" required>
                @error('email')<span class="field-hint text-accent">{{ $message }}</span>@enderror
            </label>

            <label class="field">
                <span class="field-label">Telefone / WhatsApp *</span>
                <input wire:model="phone" class="field-input" placeholder="(11) 98765-4321" required>
                @error('phone')<span class="field-hint text-accent">{{ $message }}</span>@enderror
            </label>

            <div class="grid grid-cols-3 gap-3">
                <div class="field col-span-1">
                    <span class="field-label">Estado *</span>
                    <div class="h-12 rounded-[10px] border border-signal/40 bg-signal-soft/40 px-3.5 flex items-center justify-between gap-2">
                        @if($cartState)
                            <span class="font-display font-black text-[14px] tracking-tightest text-signal">{{ $cartState->uf }}</span>
                            <button type="button"
                                    onclick="if(window.Livewire) window.Livewire.dispatch('quote-cart:open-state');"
                                    class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint hover:text-signal transition">
                                trocar
                            </button>
                        @else
                            <span class="text-ink-faint text-[13px]">Selecione</span>
                        @endif
                    </div>
                </div>
                <label class="field col-span-2">
                    <span class="field-label">Cidade *</span>
                    <input wire:model="city" class="field-input" required>
                </label>
            </div>
            @error('state_id')<span class="field-hint text-accent">{{ $message }}</span>@enderror
            @error('city')<span class="field-hint text-accent">{{ $message }}</span>@enderror

            <label class="field">
                <span class="field-label">Observação</span>
                <textarea wire:model="message" class="field-textarea" placeholder="Aplicação, urgência, condições de pagamento…"></textarea>
            </label>

            <label class="flex items-start gap-2 text-[13px] text-ink-soft pt-2">
                <input type="checkbox" wire:model="consent" class="rounded mt-0.5 border-line text-accent focus:ring-accent">
                <span>Concordo com a política de privacidade e o tratamento dos meus dados para fins de contato comercial.</span>
            </label>
            @error('consent')<span class="field-hint text-accent block">É necessário concordar com a política.</span>@enderror

            <div class="flex gap-2 pt-2">
                <button type="button" wire:click="goToStep(1)" class="btn-ghost">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M15 6l-6 6 6 6"/></svg>
                </button>
                <button type="submit" class="btn-primary flex-1" wire:loading.attr="disabled">
                    <span wire:loading.remove>Enviar cotação</span>
                    <span wire:loading>Enviando…</span>
                    <svg wire:loading.remove class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </button>
            </div>
        </form>
    @endif

    {{-- ═══ STEP 3: Confirmação ═══ --}}
    @if($step === 3 && $submitted)
        <div class="mt-5">
            <div class="card p-6 text-center">
                <div class="w-14 h-14 rounded-full bg-accent text-white mx-auto flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></svg>
                </div>
                <div class="mt-3 font-mono text-[11px] uppercase tracking-[0.08em] text-ink-faint">Protocolo</div>
                <div class="mt-1 font-display font-extrabold text-[26px] tracking-tighter">
                    #{{ $submitted->code }}
                </div>
                <p class="mt-3 text-ink-soft text-[14px] leading-relaxed">
                    Você receberá o orçamento em <strong class="text-ink">{{ $submitted->email }}</strong>. Acompanhe pelo WhatsApp para retorno mais rápido.
                </p>
            </div>

            @if($submitted->representative)
                <div class="card p-4 mt-3 text-left">
                    <div class="font-mono text-[10px] uppercase tracking-[0.08em] text-ink-faint">Representante regional</div>
                    <div class="font-semibold text-[15px] mt-1">{{ $submitted->representative->name }}</div>
                    @if($submitted->representative->phone)
                        <div class="text-[13px] text-ink-soft mt-0.5">{{ $submitted->representative->phone }}</div>
                    @endif
                </div>
            @endif

            @if($submitted->representative?->whatsapp)
                <a href="https://wa.me/{{ $submitted->representative->whatsapp }}?text={{ urlencode('Olá! Acabei de enviar a cotação '.$submitted->code) }}"
                   target="_blank" rel="noopener"
                   onclick="window.dataLayer&&dataLayer.push({event:'whatsapp_click',source:'thankyou'})"
                   class="btn-whatsapp btn-block mt-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.05 4.91A10 10 0 0 0 4.07 18.2L3 22l3.9-1.02a10 10 0 0 0 12.15-16.07ZM12 19.5a7.5 7.5 0 0 1-3.81-1.04l-.27-.16-2.07.54.55-2.02-.18-.28A7.5 7.5 0 1 1 12 19.5Zm4.1-5.6c-.22-.11-1.32-.65-1.53-.72-.2-.07-.35-.11-.5.11-.14.22-.56.71-.7.86-.13.14-.25.16-.47.05a6.1 6.1 0 0 1-1.8-1.1 6.74 6.74 0 0 1-1.25-1.54c-.13-.22-.01-.34.1-.45.1-.1.22-.27.33-.4.11-.14.15-.24.22-.4.07-.15.04-.28-.02-.4-.05-.11-.5-1.2-.68-1.64-.18-.43-.36-.37-.5-.38h-.42a.8.8 0 0 0-.58.27 2.45 2.45 0 0 0-.78 1.85c0 1.1.8 2.15.91 2.3.11.14 1.56 2.38 3.78 3.34.53.23.94.36 1.26.46.53.16 1.02.14 1.4.09.43-.07 1.32-.54 1.5-1.06.19-.52.19-.97.13-1.06-.06-.1-.2-.15-.43-.26Z"/></svg>
                    Acompanhar por WhatsApp
                </a>
            @endif

            <a href="{{ route('site.home') }}" class="btn-ghost btn-block mt-2">Voltar para a Home</a>

            <script>window.dataLayer&&dataLayer.push({event:'quote_submit',code:'{{ $submitted->code }}'})</script>
        </div>
    @endif
</div>

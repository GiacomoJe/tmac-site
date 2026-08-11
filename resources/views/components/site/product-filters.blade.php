@props([
    'categoryTree',
    'brands',
    'selectedCats' => [],
    'selectedBrands' => [],
    'q' => '',
    // Preservados (vêm da barra horizontal)
    'selectedTmac' => [],
    'selectedMake' => null,
    'selectedModel' => null,
    'selectedYear' => null,
])

{{-- Categorias + marcas. Auto-aplica ao marcar. --}}
<form method="GET" action="{{ route('site.products') }}" class="product-filters"
      x-data="{
          pending: false,
          timer: null,
          queue() {
              this.pending = true;
              clearTimeout(this.timer);
              this.timer = setTimeout(() => $el.submit(), 650);
          }
      }"
      x-on:change="queue()">

    {{-- Preserva filtros da barra horizontal --}}
    @if($q)<input type="hidden" name="q" value="{{ $q }}">@endif
    @foreach($selectedTmac as $t)<input type="hidden" name="tmac[]" value="{{ $t }}">@endforeach
    @if($selectedMake)<input type="hidden" name="moto" value="{{ $selectedMake }}">@endif
    @if($selectedModel)<input type="hidden" name="modelo" value="{{ $selectedModel }}">@endif
    @if($selectedYear)<input type="hidden" name="ano" value="{{ $selectedYear }}">@endif

    @php $activeCount = count($selectedCats) + count($selectedBrands); @endphp
    <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="font-mono text-[11px] uppercase tracking-[0.08em] text-ink-faint">
            Filtros
            @if($activeCount > 0)
                <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-signal text-white text-[10px] font-bold">{{ $activeCount }}</span>
            @endif
        </div>
        @if($activeCount > 0)
            @php
                $keep = array_filter([
                    'q' => $q ?: null,
                    'tmac' => $selectedTmac,
                    'moto' => $selectedMake,
                    'modelo' => $selectedModel,
                    'ano' => $selectedYear,
                ]);
            @endphp
            <a href="{{ route('site.products', $keep) }}"
               class="font-mono text-[10px] uppercase tracking-[0.06em] text-accent hover:underline">
                Limpar
            </a>
        @endif
    </div>

    {{-- ═══ CATEGORIAS ═══ --}}
    @if($categoryTree->isNotEmpty())
        <div class="mt-5"
             x-data="{
                 expanded: {
                     @foreach($categoryTree as $root)
                         '{{ $root->slug }}': @json(in_array($root->slug, $selectedCats) || $root->activeChildren->pluck('slug')->intersect($selectedCats)->isNotEmpty()),
                     @endforeach
                 },
                 toggleParent(slug, parentEl) {
                     const checked = parentEl.checked;
                     this.expanded[slug] = true;
                     const wrapper = parentEl.closest('[data-cat-wrap]');
                     if (!wrapper) return;
                     wrapper.querySelectorAll('input[data-sub-of=' + slug + ']').forEach(el => { el.checked = checked; });
                 },
                 syncParent(slug, parentEl) {
                     const wrapper = parentEl.closest('[data-cat-wrap]');
                     if (!wrapper) return;
                     const subs = wrapper.querySelectorAll('input[data-sub-of=' + slug + ']');
                     if (subs.length === 0) return;
                     if (Array.from(subs).every(el => el.checked)) parentEl.checked = true;
                 }
             }">

            <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-ink-faint mb-3 flex items-center gap-2">
                <span class="w-1 h-1 bg-signal rounded-full"></span>
                Categorias
            </div>

            <div class="space-y-1">
                @foreach($categoryTree as $root)
                    <div data-cat-wrap
                         class="rounded-lg border border-line bg-bg-elev/40 hover:border-line/80 transition"
                         :class="expanded['{{ $root->slug }}'] ? 'ring-1 ring-signal/20' : ''">
                        <div class="flex items-center gap-2 px-2.5 py-2">
                            <label class="flex-1 flex items-center gap-2.5 cursor-pointer min-w-0">
                                <input type="checkbox" name="cat[]" value="{{ $root->slug }}"
                                       @checked(in_array($root->slug, $selectedCats))
                                       x-on:change="toggleParent('{{ $root->slug }}', $event.target)"
                                       data-parent="{{ $root->slug }}"
                                       class="h-4 w-4 rounded border-line text-signal focus:ring-signal focus:ring-offset-0">
                                <span class="text-[13px] font-semibold text-ink truncate">{{ $root->name }}</span>
                                @if($root->activeChildren->isNotEmpty())
                                    <span class="font-mono text-[9px] text-ink-faint shrink-0">{{ $root->activeChildren->count() }}</span>
                                @endif
                            </label>

                            @if($root->activeChildren->isNotEmpty())
                                <button type="button" @click="expanded['{{ $root->slug }}'] = !expanded['{{ $root->slug }}']"
                                        class="w-6 h-6 rounded flex items-center justify-center text-ink-faint hover:bg-line/40 transition"
                                        :aria-expanded="expanded['{{ $root->slug }}']"
                                        aria-label="Expandir subcategorias">
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="expanded['{{ $root->slug }}'] ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                                </button>
                            @endif
                        </div>

                        @if($root->activeChildren->isNotEmpty())
                            <div x-show="expanded['{{ $root->slug }}']" x-collapse
                                 class="pl-7 pr-2.5 pb-2.5 space-y-1 border-t border-line/50">
                                @foreach($root->activeChildren as $sub)
                                    <label class="flex items-center gap-2.5 cursor-pointer py-1 group">
                                        <input type="checkbox" name="cat[]" value="{{ $sub->slug }}"
                                               @checked(in_array($sub->slug, $selectedCats) || in_array($root->slug, $selectedCats))
                                               data-sub-of="{{ $root->slug }}"
                                               x-on:change="syncParent('{{ $root->slug }}', document.querySelector('input[data-parent={{ $root->slug }}]'))"
                                               class="h-3.5 w-3.5 rounded border-line text-signal focus:ring-signal focus:ring-offset-0">
                                        <span class="text-[12.5px] text-ink-soft group-hover:text-ink transition truncate">{{ $sub->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ MARCAS ═══ --}}
    @if($brands->isNotEmpty())
        <div class="mt-6" x-data="{ showAll: false }">
            <div class="font-mono text-[10px] uppercase tracking-[0.10em] text-ink-faint mb-3 flex items-center gap-2">
                <span class="w-1 h-1 bg-accent rounded-full"></span>
                Marcas
            </div>

            <div class="space-y-1">
                @foreach($brands as $i => $b)
                    <label x-show="showAll || {{ $i }} < 6"
                           class="flex items-center gap-2.5 cursor-pointer py-1.5 px-2 rounded hover:bg-bg-elev transition group">
                        <input type="checkbox" name="brand[]" value="{{ $b->slug }}"
                               @checked(in_array($b->slug, $selectedBrands))
                               class="h-3.5 w-3.5 rounded border-line text-accent focus:ring-accent focus:ring-offset-0">
                        <span class="text-[13px] text-ink-soft group-hover:text-ink transition truncate">{{ $b->name }}</span>
                    </label>
                @endforeach
            </div>

            @if($brands->count() > 6)
                <button type="button" @click="showAll = !showAll"
                        class="mt-2 font-mono text-[10px] uppercase tracking-[0.06em] text-signal hover:underline">
                    <span x-show="!showAll">+ Ver todas ({{ $brands->count() }})</span>
                    <span x-show="showAll" x-cloak>− Mostrar menos</span>
                </button>
            @endif
        </div>
    @endif

    <div x-show="pending" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="filters-applying">
        <span class="filters-applying__spinner"></span>
        Aplicando filtros…
    </div>

    <noscript>
        <button type="submit" class="btn-primary btn-block mt-5">Aplicar filtros</button>
    </noscript>
</form>

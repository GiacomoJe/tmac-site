@php
    $socials  = \App\Models\SocialLink::visible();
    $whatsApp = \App\Models\Setting::get('whatsapp_number');
@endphp

@if($socials->isNotEmpty() || $whatsApp)
    <aside class="social-rail"
           x-data="{ visible: false }"
           x-init="
               setTimeout(() => { visible = window.scrollY > 280 }, 300);
               window.addEventListener('scroll', () => { visible = window.scrollY > 280 }, { passive: true });
           "
           :class="visible ? 'is-visible' : ''"
           aria-label="Redes sociais e contato">

        {{-- WhatsApp em destaque (se cadastrado) --}}
        @if($whatsApp)
            <a href="https://wa.me/{{ $whatsApp }}" target="_blank" rel="noopener"
               class="social-rail__btn social-rail__btn--whatsapp"
               data-label="WhatsApp"
               aria-label="Falar no WhatsApp"
               onclick="window.dataLayer&&dataLayer.push({event:'whatsapp_click',source:'rail'})">
                <x-site.social-icon platform="whatsapp" class="w-[18px] h-[18px]"/>
            </a>

            @if($socials->isNotEmpty())
                <span class="social-rail__sep"></span>
            @endif
        @endif

        {{-- Outras redes --}}
        @foreach($socials as $s)
            @continue($s->platform === 'whatsapp' && $whatsApp)  {{-- evita duplicar o WhatsApp se já tem nas socials --}}
            <a href="{{ $s->url }}" target="_blank" rel="noopener"
               class="social-rail__btn"
               data-label="{{ $s->display_label }}"
               aria-label="{{ $s->display_label }}"
               onclick="window.dataLayer&&dataLayer.push({event:'social_click',platform:'{{ $s->platform }}',source:'rail'})">
                <x-site.social-icon :platform="$s->platform" class="w-[16px] h-[16px]"/>
            </a>
        @endforeach
    </aside>
@endif

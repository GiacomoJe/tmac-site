<div>
    @if($submitted)
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-signal/15 border border-signal/30 text-white">
            <svg class="w-5 h-5 flex-shrink-0 text-signal" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></svg>
            <div class="text-[13px]">
                <strong>Inscrição confirmada!</strong>
                Você receberá as próximas novidades.
            </div>
        </div>
    @else
        <form wire:submit="submit" class="space-y-2.5">
            <div class="flex flex-col sm:flex-row gap-2">
                <input wire:model="name" class="flex-1 h-11 px-3.5 rounded-lg bg-white/10 border border-white/15 text-white placeholder:text-white/45 text-[14px] focus:outline-none focus:border-signal focus:ring-2 focus:ring-signal/30 transition" placeholder="Seu nome" required>
                <input wire:model="email" type="email" class="flex-1 h-11 px-3.5 rounded-lg bg-white/10 border border-white/15 text-white placeholder:text-white/45 text-[14px] focus:outline-none focus:border-signal focus:ring-2 focus:ring-signal/30 transition" placeholder="seu@email.com" required>
                <button type="submit" class="h-11 px-5 rounded-lg bg-accent hover:bg-accent/90 text-white text-[13px] font-semibold uppercase tracking-[0.04em] transition inline-flex items-center justify-center gap-1.5 whitespace-nowrap" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $ctaLabel }}</span>
                    <span wire:loading>Enviando…</span>
                    <svg wire:loading.remove class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </button>
            </div>

            <label class="flex items-start gap-2 text-[11px] text-white/55 leading-relaxed">
                <input type="checkbox" wire:model="consent" class="rounded mt-0.5 border-white/30 bg-white/10 text-signal focus:ring-signal">
                <span>Aceito receber novidades comerciais da TMAC por e-mail.</span>
            </label>

            @error('name')<div class="text-[11px] text-accent">{{ $message }}</div>@enderror
            @error('email')<div class="text-[11px] text-accent">{{ $message }}</div>@enderror
            @error('consent')<div class="text-[11px] text-accent">É necessário aceitar para se inscrever.</div>@enderror
        </form>
    @endif
</div>

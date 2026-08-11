<div>
    @if($title)
        <div class="font-display font-extrabold text-[18px] tracking-tightish text-ink leading-tight">{{ $title }}</div>
    @endif
    @if($subtitle)
        <p class="text-[13px] text-ink-soft mt-1.5 leading-relaxed">{{ $subtitle }}</p>
    @endif

    @if($submitted)
        <div class="mt-4 p-4 rounded-lg bg-signal-soft/40 border border-signal/30 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-signal" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></svg>
            <div class="text-[13px] text-ink">
                <strong>Recebemos!</strong> {{ $successMessage }}
            </div>
        </div>
    @else
        <form wire:submit="submit" class="mt-4 space-y-2.5">
            <input wire:model="name" class="field-input h-11" placeholder="Seu nome" required>
            <input wire:model="email" type="email" class="field-input h-11" placeholder="voce@empresa.com" required>
            <label class="flex items-start gap-2 text-[12px] text-ink-soft pt-1">
                <input type="checkbox" wire:model="consent" class="rounded mt-0.5 border-line text-signal focus:ring-signal">
                <span>Aceito receber contato comercial da TMAC.</span>
            </label>
            <button type="submit" class="btn-primary btn-block" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $ctaLabel }}</span>
                <span wire:loading>Enviando…</span>
            </button>
            @error('name')<div class="text-[11px] text-accent">{{ $message }}</div>@enderror
            @error('email')<div class="text-[11px] text-accent">{{ $message }}</div>@enderror
            @error('consent')<div class="text-[11px] text-accent">É necessário aceitar.</div>@enderror
        </form>
    @endif
</div>

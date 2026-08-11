<div>
    @if($title)
        <div class="section-marker section-marker--lg">
            <span class="section-marker__num">→</span>
            <span class="section-marker__label">{{ $title }}</span>
        </div>
    @endif

    @if($submitted)
        <div class="card p-6 mt-4 border-signal/30 bg-signal-soft/30 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-signal text-white flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></svg>
            </div>
            <div class="mt-3 font-display font-extrabold text-[18px] tracking-tightish text-ink">
                Tudo certo!
            </div>
            <p class="mt-1 text-[13px] text-ink-soft">{{ $successMessage }}</p>
            <button wire:click="$set('submitted', false)" class="mt-4 btn-ghost btn-sm">
                Enviar outra mensagem
            </button>
        </div>
    @else
        <form wire:submit="submit" class="mt-4 space-y-3">
            <div class="grid sm:grid-cols-2 gap-3">
                <label class="field">
                    <span class="field-label">Nome *</span>
                    <input wire:model="name" class="field-input" placeholder="Seu nome" required>
                    @error('name')<span class="field-hint text-accent">{{ $message }}</span>@enderror
                </label>

                <label class="field">
                    <span class="field-label">E-mail *</span>
                    <input wire:model="email" type="email" class="field-input" placeholder="voce@empresa.com" required>
                    @error('email')<span class="field-hint text-accent">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid sm:grid-cols-2 gap-3">
                <label class="field">
                    <span class="field-label">Telefone / WhatsApp</span>
                    <input wire:model="phone" class="field-input" placeholder="(11) 98765-4321">
                    @error('phone')<span class="field-hint text-accent">{{ $message }}</span>@enderror
                </label>

                @if($askCompany)
                    <label class="field">
                        <span class="field-label">Empresa</span>
                        <input wire:model="company" class="field-input" placeholder="Sua loja / oficina">
                        @error('company')<span class="field-hint text-accent">{{ $message }}</span>@enderror
                    </label>
                @endif
            </div>

            @if($askState && $states->isNotEmpty())
                <label class="field">
                    <span class="field-label">Estado</span>
                    <select wire:model="state_id" class="field-select">
                        <option value="">Selecione…</option>
                        @foreach($states as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->uf }})</option>
                        @endforeach
                    </select>
                    @error('state_id')<span class="field-hint text-accent">{{ $message }}</span>@enderror
                </label>
            @endif

            @if($askMessage)
                @if($askSubjectChoice && !empty($subjectChoices))
                    <label class="field">
                        <span class="field-label">Tipo de manifestação *</span>
                        <select wire:model="subject" class="field-select" required>
                            <option value="">Selecione…</option>
                            @foreach($subjectChoices as $choice)
                                <option value="{{ $choice }}">{{ $choice }}</option>
                            @endforeach
                        </select>
                        @error('subject')<span class="field-hint text-accent">{{ $message }}</span>@enderror
                    </label>
                @elseif(! $defaultSubject)
                    <label class="field">
                        <span class="field-label">Assunto</span>
                        <input wire:model="subject" class="field-input" placeholder="Sobre o que você quer falar?">
                        @error('subject')<span class="field-hint text-accent">{{ $message }}</span>@enderror
                    </label>
                @endif

                <label class="field">
                    <span class="field-label">Mensagem</span>
                    <textarea wire:model="message" class="field-textarea" rows="5" placeholder="Conte um pouco sobre o que você precisa…"></textarea>
                    @error('message')<span class="field-hint text-accent">{{ $message }}</span>@enderror
                </label>
            @endif

            <label class="flex items-start gap-2 text-[13px] text-ink-soft pt-2">
                <input type="checkbox" wire:model="consent" class="rounded mt-0.5 border-line text-signal focus:ring-signal">
                <span>Concordo em receber contato da equipe comercial da TMAC sobre minha solicitação.</span>
            </label>
            @error('consent')<span class="field-hint text-accent block">{{ $message }}</span>@enderror

            <button type="submit" class="btn-primary btn-block mt-3" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $ctaLabel }}</span>
                <span wire:loading>Enviando…</span>
                <svg wire:loading.remove class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
            </button>

            <p class="text-[11px] text-ink-faint text-center mt-2 leading-relaxed">
                Resposta em até 1 dia útil · Sem spam · Apenas para atacado CNPJ
            </p>
        </form>
    @endif
</div>

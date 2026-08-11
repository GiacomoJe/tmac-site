<?php

namespace App\Livewire\Site;

use App\Mail\LeadCaptured;
use App\Models\Lead;
use App\Models\State;
use App\Services\RdStation;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LeadForm extends Component
{
    // ── Configuração (vinda do Blade) ───────────────────────────
    public string $source = Lead::SOURCE_CONTACT;
    public string $variant = 'full'; // full | compact | inline (footer)
    public ?string $title = null;
    public ?string $subtitle = null;
    public ?string $defaultSubject = null;
    public ?string $defaultInterest = null;
    public bool $askMessage = true;
    public bool $askCompany = true;
    public bool $askState = true;
    public bool $askSubjectChoice = false;        // mostra select com opções pré-definidas
    public array $subjectChoices = [];            // ['Sugestão', 'Reclamação', ...]
    public ?string $recipientSettingKey = null;   // chave do Setting com o e-mail destino
    public string $ctaLabel = 'Enviar';
    public string $successMessage = 'Recebemos seu contato! Em breve falamos com você.';

    // ── Campos do formulário ────────────────────────────────────
    #[Validate('required|string|max:150')]
    public string $name = '';

    #[Validate('required|email|max:150')]
    public string $email = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    #[Validate('nullable|string|max:150')]
    public string $company = '';

    #[Validate('nullable|exists:states,id')]
    public ?int $state_id = null;

    #[Validate('nullable|string|max:120')]
    public string $subject = '';

    #[Validate('nullable|string|max:2000')]
    public string $message = '';

    #[Validate('accepted')]
    public bool $consent = false;

    // ── Estado da UI ────────────────────────────────────────────
    public bool $submitted = false;

    public function mount(): void
    {
        if ($this->defaultSubject) {
            $this->subject = $this->defaultSubject;
        }
    }

    public function rules(): array
    {
        $base = [
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|max:150',
            'phone'    => 'nullable|string|max:30',
            'consent'  => 'accepted',
        ];
        if ($this->askCompany) {
            $base['company'] = 'nullable|string|max:150';
        }
        if ($this->askState) {
            $base['state_id'] = 'nullable|exists:states,id';
        }
        if ($this->askMessage) {
            $base['message'] = 'nullable|string|max:2000';
            $base['subject'] = 'nullable|string|max:120';
        }
        // Newsletter (compact/inline) — só nome+email
        if (in_array($this->variant, ['compact', 'inline'])) {
            unset($base['phone']);
        }
        return $base;
    }

    public function submit(RdStation $rd): void
    {
        $this->validate();

        $lead = Lead::create([
            'name'        => $this->name,
            'email'       => mb_strtolower(trim($this->email)),
            'phone'       => $this->phone ?: null,
            'company'     => $this->company ?: null,
            'state_id'    => $this->state_id,
            'subject'     => $this->subject ?: null,
            'message'     => $this->message ?: null,
            'interest'    => $this->defaultInterest,
            'source'      => $this->source,
            'source_url'  => url()->previous(),
            'utm_payload' => session('utm', []),
            'ip'          => request()->ip(),
            'user_agent'  => substr((string) request()->userAgent(), 0, 500),
        ]);

        // Notifica por e-mail (best-effort)
        // Se recipientSettingKey foi passado, usa o Setting; caso contrário, comercial
        try {
            $to = $this->recipientSettingKey
                ? (\App\Models\Setting::get($this->recipientSettingKey) ?: config('quote.commercial_email'))
                : config('quote.commercial_email');

            if ($to) {
                Mail::to($to)->send(new LeadCaptured($lead->fresh('state')));
            }
        } catch (\Throwable $e) {
            logger()->warning('Lead email failed: ' . $e->getMessage());
        }

        // RD Station (best-effort)
        $rd->trackLead($lead);

        $this->submitted = true;
        $this->dispatch('lead-captured', source: $this->source);

        // Reset campos depois do submit (pra permitir nova captura no mesmo widget)
        $this->reset(['name', 'email', 'phone', 'company', 'state_id', 'subject', 'message', 'consent']);
    }

    public function render()
    {
        $view = match ($this->variant) {
            'inline'  => 'livewire.site.lead-form-inline',
            'compact' => 'livewire.site.lead-form-compact',
            default   => 'livewire.site.lead-form',
        };

        return view($view, [
            'states' => $this->askState ? State::orderBy('name')->get(['id', 'name', 'uf']) : collect(),
        ]);
    }
}

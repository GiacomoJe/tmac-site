<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Wrapper simples para envio de eventos ao RD Station Marketing.
 * Em produção, mover para um Job (queueable) e usar token privado OAuth.
 */
class RdStation
{
    public function __construct(
        private readonly ?string $publicToken,
        private readonly bool $enabled,
    ) {}

    public function trackQuoteSubmit(QuoteRequest $request): void
    {
        if (! $this->isEnabled()) return;

        $payload = [
            'event_type' => 'CONVERSION',
            'event_family' => 'CDP',
            'payload' => [
                'conversion_identifier' => 'solicitacao-cotacao',
                'name' => $request->customer_name,
                'email' => $request->email,
                'mobile_phone' => $request->phone,
                'company_name' => $request->company,
                'state' => optional($request->state)->uf,
                'city' => $request->city,
                'cf_codigo_cotacao' => $request->code,
                'cf_qtd_produtos' => $request->items()->count(),
            ],
        ];

        $this->dispatch($payload, "quote#{$request->id}");
    }

    /**
     * Envia um lead capturado em formulário/widget para o RD Station.
     * O `conversion_identifier` é montado a partir da origem do lead.
     */
    public function trackLead(Lead $lead): bool
    {
        if (! $this->isEnabled()) return false;

        $identifier = match ($lead->source) {
            Lead::SOURCE_CONTACT    => 'fale-conosco',
            Lead::SOURCE_NEWSLETTER => 'newsletter',
            Lead::SOURCE_REPRESENT  => 'quero-ser-representante',
            Lead::SOURCE_CATALOG    => 'download-catalogo',
            Lead::SOURCE_PRODUCT    => 'duvida-produto',
            Lead::SOURCE_FOOTER     => 'newsletter-footer',
            default                 => 'site-tmac-' . $lead->source,
        };

        $payload = [
            'event_type'   => 'CONVERSION',
            'event_family' => 'CDP',
            'payload' => array_filter([
                'conversion_identifier' => $identifier,
                'name'         => $lead->name,
                'email'        => $lead->email,
                'mobile_phone' => $lead->phone,
                'company_name' => $lead->company,
                'state'        => optional($lead->state)->uf,
                'city'         => $lead->city,
                'cf_origem'    => $lead->source,
                'cf_assunto'   => $lead->subject,
                'cf_interesse' => $lead->interest,
                'cf_url'       => $lead->source_url,
            ], fn ($v) => filled($v)),
        ];

        $ok = $this->dispatch($payload, "lead#{$lead->id}");

        if ($ok) {
            $lead->forceFill(['rd_synced_at' => now()])->save();
        }

        return $ok;
    }

    private function isEnabled(): bool
    {
        return $this->enabled && filled($this->publicToken);
    }

    private function dispatch(array $payload, string $context): bool
    {
        try {
            $response = Http::timeout(8)
                ->post("https://api.rd.services/platform/conversions?api_key={$this->publicToken}", $payload);

            if ($response->failed()) {
                Log::warning('RdStation rejected event', [
                    'context' => $context,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::warning('RdStation event failed', ['e' => $e->getMessage(), 'context' => $context]);
            return false;
        }
    }
}

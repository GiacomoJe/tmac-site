<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Novo lead — TMAC</title></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #F5F2EA; padding: 24px; color: #1A1B1F;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #D9D8D2; border-radius: 8px; overflow: hidden;">
        <div style="background: #1A1B1F; color: #fff; padding: 20px 24px;">
            <div style="font-size: 11px; letter-spacing: 0.1em; color: #888; text-transform: uppercase;">TMAC · Captação</div>
            <h1 style="font-size: 22px; margin: 4px 0 0; font-weight: 800;">Novo lead — {{ $lead->source_label }}</h1>
        </div>

        <div style="padding: 24px;">
            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; font-size: 14px;">
                <tr><td style="padding: 6px 0; color: #666; width: 30%;">Nome</td><td><strong>{{ $lead->name }}</strong></td></tr>
                <tr><td style="padding: 6px 0; color: #666;">E-mail</td><td><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></td></tr>
                @if($lead->phone)
                    <tr><td style="padding: 6px 0; color: #666;">Telefone</td><td>{{ $lead->phone }}</td></tr>
                @endif
                @if($lead->company)
                    <tr><td style="padding: 6px 0; color: #666;">Empresa</td><td>{{ $lead->company }}</td></tr>
                @endif
                @if($lead->state)
                    <tr><td style="padding: 6px 0; color: #666;">Estado</td><td>{{ $lead->state->name }} ({{ $lead->state->uf }})</td></tr>
                @endif
                @if($lead->city)
                    <tr><td style="padding: 6px 0; color: #666;">Cidade</td><td>{{ $lead->city }}</td></tr>
                @endif
                @if($lead->subject)
                    <tr><td style="padding: 6px 0; color: #666;">Assunto</td><td>{{ $lead->subject }}</td></tr>
                @endif
                @if($lead->interest)
                    <tr><td style="padding: 6px 0; color: #666;">Interesse</td><td>{{ $lead->interest }}</td></tr>
                @endif
                <tr><td style="padding: 6px 0; color: #666;">Origem</td><td>{{ $lead->source_label }}</td></tr>
                @if($lead->source_url)
                    <tr><td style="padding: 6px 0; color: #666;">Página</td><td style="font-size: 12px; color: #888;">{{ $lead->source_url }}</td></tr>
                @endif
            </table>

            @if($lead->message)
                <div style="margin-top: 20px; padding: 16px; background: #F5F2EA; border-left: 3px solid #004DFF; border-radius: 0 4px 4px 0;">
                    <div style="font-size: 11px; letter-spacing: 0.08em; color: #666; text-transform: uppercase; margin-bottom: 8px;">Mensagem</div>
                    <div style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $lead->message }}</div>
                </div>
            @endif

            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8E7E2; font-size: 11px; color: #888;">
                Lead #{{ $lead->id }} · {{ $lead->created_at->format('d/m/Y H:i') }} · IP {{ $lead->ip }}
            </div>
        </div>
    </div>
</body>
</html>

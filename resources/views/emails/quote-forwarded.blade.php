<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Cotação encaminhada — TMAC</title></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #F5F2EA; padding: 24px; color: #1A1B1F;">
    <div style="max-width: 640px; margin: 0 auto; background: #fff; border: 1px solid #D9D8D2; border-radius: 8px; overflow: hidden;">

        <div style="background: #1A1B1F; color: #fff; padding: 20px 24px; border-left: 4px solid #004DFF;">
            <div style="font-size: 11px; letter-spacing: 0.1em; color: #888; text-transform: uppercase;">TMAC · Cotação encaminhada</div>
            <h1 style="font-size: 22px; margin: 4px 0 0; font-weight: 800;">
                Olá, {{ $representative->name }} 👋
            </h1>
            <div style="font-size: 13px; margin-top: 6px; opacity: 0.85;">
                Uma nova cotação foi direcionada para você. Código: <strong>#{{ $request->code }}</strong>
            </div>
        </div>

        @if($adminMessage)
            <div style="margin: 0; padding: 16px 24px; background: #E6EDFF; border-bottom: 1px solid #D9D8D2;">
                <div style="font-size: 11px; letter-spacing: 0.08em; color: #004DFF; text-transform: uppercase; margin-bottom: 6px; font-weight: 600;">Observação do admin</div>
                <div style="font-size: 14px; line-height: 1.6; white-space: pre-wrap; color: #1A1B1F;">{{ $adminMessage }}</div>
            </div>
        @endif

        <div style="padding: 24px;">
            <div style="font-size: 11px; letter-spacing: 0.08em; color: #888; text-transform: uppercase; margin-bottom: 12px;">Dados do cliente</div>
            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; font-size: 14px;">
                <tr><td style="padding: 5px 0; color: #666; width: 32%;">Nome</td><td><strong>{{ $request->customer_name }}</strong></td></tr>
                <tr><td style="padding: 5px 0; color: #666;">Empresa</td><td>{{ $request->company }}</td></tr>
                <tr><td style="padding: 5px 0; color: #666;">CNPJ</td><td>{{ $request->cnpj }}</td></tr>
                <tr><td style="padding: 5px 0; color: #666;">Segmento</td><td>{{ \App\Models\QuoteRequest::SEGMENTS[$request->segment] ?? $request->segment }}</td></tr>
                <tr><td style="padding: 5px 0; color: #666;">E-mail</td><td><a href="mailto:{{ $request->email }}">{{ $request->email }}</a></td></tr>
                <tr><td style="padding: 5px 0; color: #666;">Telefone</td><td>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $request->phone) }}" style="color: #25D366;">{{ $request->phone }}</a>
                </td></tr>
                @if($request->state)
                    <tr><td style="padding: 5px 0; color: #666;">Localização</td><td>{{ $request->city }} / {{ $request->state->uf }}</td></tr>
                @endif
            </table>

            @if($request->message)
                <div style="margin-top: 18px; padding: 14px; background: #F5F2EA; border-left: 3px solid #1A1B1F; border-radius: 0 4px 4px 0;">
                    <div style="font-size: 11px; letter-spacing: 0.08em; color: #666; text-transform: uppercase; margin-bottom: 6px;">Mensagem do cliente</div>
                    <div style="font-size: 14px; line-height: 1.6; white-space: pre-wrap;">{{ $request->message }}</div>
                </div>
            @endif

            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8E7E2;">
                <div style="font-size: 11px; letter-spacing: 0.08em; color: #888; text-transform: uppercase; margin-bottom: 12px;">Itens solicitados ({{ $request->items->count() }})</div>
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; font-size: 13px; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F5F2EA;">
                            <th style="padding: 8px 10px; text-align: left; font-weight: 600; border-bottom: 1px solid #D9D8D2;">Produto</th>
                            <th style="padding: 8px 10px; text-align: left; font-weight: 600; border-bottom: 1px solid #D9D8D2;">SKU</th>
                            <th style="padding: 8px 10px; text-align: center; font-weight: 600; border-bottom: 1px solid #D9D8D2; width: 60px;">Qtd</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->items as $item)
                            <tr>
                                <td style="padding: 8px 10px; border-bottom: 1px solid #F0EFE9;">{{ $item->product_name_snapshot }}</td>
                                <td style="padding: 8px 10px; border-bottom: 1px solid #F0EFE9; font-family: monospace; font-size: 12px; color: #666;">{{ $item->product_sku_snapshot }}</td>
                                <td style="padding: 8px 10px; border-bottom: 1px solid #F0EFE9; text-align: center; font-weight: 600;">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 28px; padding: 14px; background: #E11D2A; color: #fff; border-radius: 6px; text-align: center;">
                <div style="font-size: 12px; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 4px; opacity: 0.85;">Próximo passo</div>
                <div style="font-size: 15px; font-weight: 600;">Entre em contato direto com o cliente o quanto antes.</div>
            </div>

            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #E8E7E2; font-size: 11px; color: #888;">
                Cotação #{{ $request->code }} · Criada em {{ $request->created_at->format('d/m/Y H:i') }}
                @if($request->forwarded_count > 1)
                    · Reencaminhada {{ $request->forwarded_count }}x
                @endif
            </div>
        </div>
    </div>
</body>
</html>

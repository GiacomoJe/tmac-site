@php /** @var \App\Models\QuoteRequest $request */ @endphp
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Nova Cotação #{{ $request->code }}</title></head>
<body style="font-family: -apple-system,Segoe UI,sans-serif;color:#222;line-height:1.5;max-width:640px;margin:0 auto;padding:24px">
    <h2 style="color:#1f4fdf">Nova solicitação de cotação</h2>
    <p><strong>Código:</strong> {{ $request->code }}<br>
       <strong>Recebida em:</strong> {{ $request->created_at->format('d/m/Y H:i') }}</p>

    <h3>Cliente</h3>
    <p>
        <strong>{{ $request->customer_name }}</strong>
        @if($request->company) — {{ $request->company }} @endif <br>
        E-mail: {{ $request->email }}<br>
        Telefone: {{ $request->phone }}<br>
        Local: {{ $request->city }} / {{ optional($request->state)->uf }}
    </p>

    @if($request->message)
        <p><strong>Mensagem:</strong><br>{!! nl2br(e($request->message)) !!}</p>
    @endif

    @if($request->representative)
        <p><strong>Representante atribuído:</strong> {{ $request->representative->name }} ({{ $request->representative->email ?? '-' }})</p>
    @endif

    <h3>Itens ({{ $request->items->count() }})</h3>
    <table cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse;width:100%;border-color:#ddd">
        <thead style="background:#f5f5f5">
            <tr><th align="left">SKU</th><th align="left">Produto</th><th align="right">Qtd</th><th align="left">Obs</th></tr>
        </thead>
        <tbody>
            @foreach($request->items as $item)
                <tr>
                    <td>{{ $item->product_sku_snapshot }}</td>
                    <td>{{ $item->product_name_snapshot }}</td>
                    <td align="right">{{ $item->quantity }}</td>
                    <td>{{ $item->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr style="margin-top:24px">
    <p style="font-size:12px;color:#777">
        Acesse o painel para responder esta cotação: {{ config('app.url') }}/admin/quote-requests/{{ $request->id }}
    </p>
</body></html>

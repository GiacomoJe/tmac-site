<?php

namespace App\Livewire\Cliente;

use App\Models\Cliente;
use App\Models\SalesCatalogItem;
use App\Models\SalesOrder;
use App\Models\SalesPriceList;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Tela principal da Área do Cliente — equivalente ao app "Tabela de Vendas"
 * antigo: busca de itens por tabela/estado, monta o pedido (quantidade + desconto
 * por linha) com total ao vivo e salva o pedido (fica registrado em sales_orders,
 * visível no painel em Tabela de Vendas → Pedidos).
 *
 * Os downloads em .xlsx do app antigo (baixar tabela em branco / baixar o
 * pedido) foram desativados por enquanto a pedido — só "salvar pedido" fica
 * disponível. A geração desses arquivos continua existindo em
 * App\Services\SalesTable\SalesTableExporter, caso se decida reativar depois.
 */
class TabelaVendas extends Component
{
    public ?SalesPriceList $priceList = null;

    /** Códigos de tabela (estado) disponíveis na versão publicada. */
    public array $tabelas = [];

    public string $tabela = '';

    public string $aba = 'busca'; // busca | pedido

    public string $q1 = '';

    public string $q2 = '';

    public string $filtro = 'todos'; // todos | promo | extra

    public int $mostrar = 40;

    /** cod => quantidade */
    public array $qtds = [];

    /** cod => percentual de desconto */
    public array $descontos = [];

    // Dados do cliente para o pedido — pré-preenchidos do cadastro, editáveis.
    public string $razaoSocial = '';

    public string $cnpj = '';

    public string $responsavel = '';

    public string $telefone = '';

    public string $email = '';

    public string $cnpjTransportadora = '';

    public string $transportadora = '';

    public string $vendedor = '';

    public string $prazoPagamento = '';

    public string $observacoes = '';

    public ?int $pedidoSalvoId = null;

    public function mount(): void
    {
        $this->priceList = SalesPriceList::active();

        if (! $this->priceList) {
            return;
        }

        $this->tabelas = $this->priceList->states()->pluck('code')->all();

        /** @var Cliente|null $cliente */
        $cliente = Auth::guard('cliente')->user();
        $preferida = $cliente?->tabela_padrao;
        $this->tabela = ($preferida && in_array($preferida, $this->tabelas, true))
            ? $preferida
            : (string) ($this->tabelas[0] ?? '');

        $this->razaoSocial = (string) ($cliente?->company ?? '');
        $this->cnpj = (string) ($cliente?->cnpj ?? '');
        $this->responsavel = (string) ($cliente?->name ?? '');
        $this->telefone = (string) ($cliente?->phone ?? $cliente?->whatsapp ?? '');
        $this->email = (string) ($cliente?->email ?? '');
    }

    public function updatedTabela(): void
    {
        $this->mostrar = 40;
    }

    public function updatedQ1(): void
    {
        $this->mostrar = 40;
    }

    public function updatedQ2(): void
    {
        $this->mostrar = 40;
    }

    public function filtrar(string $filtro): void
    {
        $this->filtro = in_array($filtro, ['todos', 'promo', 'extra'], true) ? $filtro : 'todos';
        $this->mostrar = 40;
    }

    public function limparBusca(): void
    {
        $this->q1 = '';
        $this->q2 = '';
        $this->mostrar = 40;
    }

    public function mudarAba(string $aba): void
    {
        $this->aba = $aba === 'pedido' ? 'pedido' : 'busca';
    }

    public function mostrarMais(): void
    {
        $this->mostrar += 40;
    }

    public function limparPedido(): void
    {
        $this->qtds = [];
        $this->descontos = [];
    }

    /**
     * Ações explícitas (em vez de wire:model.\*="qtds.{cod}") de propósito: o código
     * do produto (cod) pode conter caracteres como "." que o Livewire interpretaria
     * como caminho aninhado num binding automático. Passando o cod como argumento
     * comum do método evitamos esse problema, seja qual for o formato do SKU.
     */
    public function setQtd(string $cod, mixed $value): void
    {
        $qtd = max(0, (int) $value);
        if ($qtd > 0) {
            $this->qtds[$cod] = $qtd;
        } else {
            unset($this->qtds[$cod], $this->descontos[$cod]);
        }
    }

    public function setDesconto(string $cod, mixed $value): void
    {
        $item = $this->catalogItem($cod);
        if ($item && $item->promo) {
            $this->descontos[$cod] = 0;

            return;
        }

        $this->descontos[$cod] = max(0, min(100, (float) str_replace(',', '.', (string) $value)));
    }

    protected function catalogItem(string $cod): ?SalesCatalogItem
    {
        if (! $this->priceList) {
            return null;
        }

        return SalesCatalogItem::query()
            ->where('sales_price_list_id', $this->priceList->id)
            ->forTabela($this->tabela)
            ->where('cod', $cod)
            ->first();
    }

    public function valorFinalDe(SalesCatalogItem $item): float
    {
        $desconto = (float) ($this->descontos[$item->cod] ?? 0);

        return $item->valorFinal($desconto);
    }

    protected function baseQuery(): Builder
    {
        return SalesCatalogItem::query()
            ->when($this->priceList, fn (Builder $q) => $q->where('sales_price_list_id', $this->priceList->id))
            ->forTabela($this->tabela)
            ->search($this->q1 ?: null, $this->q2 ?: null)
            ->promoFilter($this->filtro);
    }

    /**
     * @return Collection<int, SalesCatalogItem>
     */
    protected function pedidoItens(): Collection
    {
        $cods = array_keys(array_filter($this->qtds, fn ($q) => (int) $q > 0));

        if (empty($cods) || ! $this->priceList) {
            return collect();
        }

        return SalesCatalogItem::query()
            ->where('sales_price_list_id', $this->priceList->id)
            ->forTabela($this->tabela)
            ->whereIn('cod', $cods)
            ->orderBy('descricao')
            ->get()
            ->map(function (SalesCatalogItem $item) {
                $qtd = (int) ($this->qtds[$item->cod] ?? 0);
                $desconto = (float) ($this->descontos[$item->cod] ?? 0);
                $valorFinal = $item->valorFinal($desconto);

                $item->qtd_pedido = $qtd;
                $item->desconto_pedido = $desconto;
                $item->valor_final_calc = $valorFinal;
                $item->total_calc = round($valorFinal * $qtd, 2);

                return $item;
            });
    }

    protected function resumo(Collection $pedidoItens): array
    {
        return [
            'qtd' => $pedidoItens->count(),
            'pecas' => (int) $pedidoItens->sum('qtd_pedido'),
            'total' => (float) $pedidoItens->filter(fn ($i) => ! $i->bloqueado)->sum('total_calc'),
            'totalBloqueado' => (float) $pedidoItens->filter(fn ($i) => $i->bloqueado)->sum('total_calc'),
        ];
    }

    public function salvarPedido(): void
    {
        $this->pedidoSalvoId = null;

        $pedidoItens = $this->pedidoItens();

        if ($pedidoItens->isEmpty()) {
            $this->addError('pedido', 'Informe a quantidade de pelo menos um item antes de salvar o pedido.');
            $this->aba = 'pedido';

            return;
        }

        // Já muda pra aba do pedido ANTES de validar: se faltar preencher algum
        // dado obrigatório (razão social, responsável etc.), o cliente precisa
        // estar vendo o formulário quando o erro aparecer — mesmo que tenha
        // clicado em "Salvar pedido" ainda na aba de busca (o botão fica visível
        // nas duas abas).
        $this->aba = 'pedido';

        $this->validate([
            'razaoSocial' => 'required|string|max:150',
            'responsavel' => 'required|string|max:150',
            'telefone' => 'required|string|max:30',
            'email' => 'required|email|max:150',
        ], [], [
            'razaoSocial' => 'razão social',
            'responsavel' => 'responsável',
            'telefone' => 'telefone',
            'email' => 'e-mail',
        ]);

        $resumo = $this->resumo($pedidoItens);

        /** @var Cliente $cliente */
        $cliente = Auth::guard('cliente')->user();

        $order = SalesOrder::create([
            'cliente_id' => $cliente->id,
            'sales_price_list_id' => $this->priceList?->id,
            'tabela' => $this->tabela,
            'razao_social' => $this->razaoSocial,
            'cnpj' => $this->cnpj ?: null,
            'responsavel' => $this->responsavel,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'cnpj_transportadora' => $this->cnpjTransportadora ?: null,
            'transportadora' => $this->transportadora ?: null,
            'vendedor' => $this->vendedor ?: null,
            'prazo_pagamento' => $this->prazoPagamento ?: null,
            'observacoes' => $this->observacoes ?: null,
            'total' => $resumo['total'],
            'total_bloqueado' => $resumo['totalBloqueado'],
            'itens_count' => $resumo['qtd'],
            'pecas_count' => $resumo['pecas'],
            'ip' => request()->ip(),
        ]);

        foreach ($pedidoItens as $item) {
            $order->items()->create([
                'cod' => $item->cod,
                'descricao' => $item->descricao,
                'marca' => $item->marca,
                'preco_tabela' => $item->preco,
                'desconto_percent' => $item->desconto_pedido,
                'quantidade' => $item->qtd_pedido,
                'valor_final' => $item->valor_final_calc,
                'total' => $item->total_calc,
                'bloqueado' => $item->bloqueado,
            ]);
        }

        $this->pedidoSalvoId = $order->id;
        $this->qtds = [];
        $this->descontos = [];
        $this->dispatch('pedido-salvo', code: $order->code);
    }

    public function render(): View
    {
        $itens = null;
        $total = 0;

        if ($this->priceList) {
            $query = $this->baseQuery()->orderBy('descricao');
            $total = (clone $query)->count();
            $itens = $query->limit($this->mostrar)->get();
        }

        $pedidoItens = $this->pedidoItens();
        $resumo = $this->resumo($pedidoItens);

        return view('livewire.cliente.tabela-vendas', [
            'itens' => $itens,
            'itensTotal' => $total,
            'pedidoItens' => $pedidoItens,
            'resumo' => $resumo,
        ]);
    }
}

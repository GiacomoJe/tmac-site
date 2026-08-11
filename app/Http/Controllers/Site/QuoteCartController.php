<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Livewire\Site\QuoteCart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteCartController extends Controller
{
    public function add(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'nullable|integer|min:1|max:9999',
        ]);

        // Confirma que o produto está ativo
        $product = Product::active()->findOrFail($data['product_id']);
        $qty = (int) ($data['quantity'] ?? 1);

        // Regra obrigatória: precisa ter UF selecionada antes de qualquer adição.
        // Retorna 200 com flag needs_state — o JS vai abrir o modal.
        if (! QuoteCart::hasState()) {
            return response()->json([
                'ok'           => false,
                'needs_state'  => true,
                'product_id'   => $product->id,
                'quantity'     => $qty,
                'message'      => 'Selecione o estado de origem antes de adicionar produtos.',
            ]);
        }

        QuoteCart::add($product->id, $qty);

        return response()->json([
            'ok'        => true,
            'count'     => QuoteCart::count(),
            'distinct'  => QuoteCart::distinctCount(),
            'progress'  => QuoteCart::progressPercent(),
            'meets_min' => QuoteCart::meetsMinimum(),
            'state_uf'  => QuoteCart::stateUf(),
            'product'   => [
                'id'   => $product->id,
                'sku'  => $product->sku,
                'name' => $product->name,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer|min:0|max:9999',
        ]);

        QuoteCart::update($data['product_id'], $data['quantity']);

        return response()->json([
            'ok'        => true,
            'count'     => QuoteCart::count(),
            'progress'  => QuoteCart::progressPercent(),
            'meets_min' => QuoteCart::meetsMinimum(),
        ]);
    }

    public function remove(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
        ]);

        QuoteCart::remove($data['product_id']);

        return response()->json([
            'ok'        => true,
            'count'     => QuoteCart::count(),
            'progress'  => QuoteCart::progressPercent(),
            'meets_min' => QuoteCart::meetsMinimum(),
        ]);
    }
}

<?php

namespace App\Livewire\Site;

use App\Models\Product;
use App\Models\State;
use Illuminate\Support\Facades\Session;

/**
 * Carrinho de cotação — armazenado em session.
 *
 * Estrutura na sessão:
 *   quote_cart.products    => [id => ['quantity' => N, 'notes' => '']]
 *   quote_cart.state_uf    => 'SP'
 *
 * Preço (quote_price) é resolvido SEMPRE no servidor — nunca exposto ao cliente final.
 */
class QuoteCart
{
    public const SESSION_KEY = 'quote_cart';

    /* ──────────────────────────── ITENS ──────────────────────────── */

    public static function items(): array
    {
        return Session::get(self::SESSION_KEY.'.products', []);
    }

    public static function count(): int
    {
        return array_sum(array_column(static::items(), 'quantity'));
    }

    public static function distinctCount(): int
    {
        return count(static::items());
    }

    public static function add(int $productId, int $quantity = 1): void
    {
        $items = static::items();
        $items[$productId] = [
            'quantity' => ($items[$productId]['quantity'] ?? 0) + $quantity,
            'notes' => $items[$productId]['notes'] ?? '',
        ];
        Session::put(self::SESSION_KEY.'.products', $items);
    }

    public static function update(int $productId, int $quantity, ?string $notes = null): void
    {
        $items = static::items();
        if ($quantity <= 0) {
            unset($items[$productId]);
        } else {
            $items[$productId] = [
                'quantity' => $quantity,
                'notes' => $notes ?? ($items[$productId]['notes'] ?? ''),
            ];
        }
        Session::put(self::SESSION_KEY.'.products', $items);
    }

    public static function remove(int $productId): void
    {
        $items = static::items();
        unset($items[$productId]);
        Session::put(self::SESSION_KEY.'.products', $items);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<Product> */
    public static function products()
    {
        $ids = array_keys(static::items());
        return $ids ? Product::with('brand')->whereIn('id', $ids)->get() : collect();
    }

    /* ──────────────────────────── UF / ESTADO ──────────────────────────── */

    public static function stateUf(): ?string
    {
        return Session::get(self::SESSION_KEY.'.state_uf');
    }

    public static function setStateUf(string $uf): void
    {
        Session::put(self::SESSION_KEY.'.state_uf', strtoupper($uf));
    }

    public static function clearState(): void
    {
        Session::forget(self::SESSION_KEY.'.state_uf');
    }

    public static function hasState(): bool
    {
        return ! empty(static::stateUf());
    }

    public static function state(): ?State
    {
        $uf = static::stateUf();
        return $uf ? State::where('uf', $uf)->first() : null;
    }

    /* ──────────────────────────── MÍNIMO / PROGRESSO ──────────────────────────── */

    /**
     * Subtotal INTERNO em R$ (server-side). Nunca exposto ao cliente.
     * Soma quote_price * quantity de cada item.
     */
    public static function subtotal(): float
    {
        $items = static::items();
        if (empty($items)) {
            return 0;
        }

        $ids = array_keys($items);
        $prices = Product::whereIn('id', $ids)
            ->pluck('quote_price', 'id')
            ->all();

        $total = 0.0;
        foreach ($items as $id => $row) {
            $price = (float) ($prices[$id] ?? 0);
            $qty   = (int) ($row['quantity'] ?? 0);
            $total += $price * $qty;
        }
        return $total;
    }

    /** Mínimo do estado selecionado (ou fallback config). */
    public static function minimum(): float
    {
        $state = static::state();
        if ($state) {
            return $state->effective_minimum;
        }
        return (float) config('quote.default_minimum', 2000);
    }

    /** Percentual atingido (0–100, saturado). */
    public static function progressPercent(): int
    {
        $min = static::minimum();
        if ($min <= 0) {
            return 100;
        }
        $pct = (int) floor((static::subtotal() / $min) * 100);
        return min($pct, 100);
    }

    public static function meetsMinimum(): bool
    {
        return static::subtotal() >= static::minimum();
    }
}

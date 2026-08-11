<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\Representative;
use App\Models\State;

/**
 * Roteia uma solicitação de cotação para o representante correto com base na UF.
 * Estratégia (em ordem): is_primary → primeiro ativo no estado → fallback nulo.
 */
class QuoteRouter
{
    public function assign(QuoteRequest $request): ?Representative
    {
        if (! $request->state_id) {
            return null;
        }

        $state = State::with('representatives')->find($request->state_id);

        if (! $state) {
            return null;
        }

        $rep = $state->representatives()
            ->where('is_active', true)
            ->wherePivot('is_primary', true)
            ->first();

        $rep ??= $state->representatives()
            ->where('is_active', true)
            ->first();

        if ($rep) {
            $request->assigned_representative_id = $rep->id;
            $request->save();
        }

        return $rep;
    }
}

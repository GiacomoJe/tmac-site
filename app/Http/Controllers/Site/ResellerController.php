<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use App\Models\State;
use App\Services\SeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ResellerController extends Controller
{
    public function index(SeoMeta $seo)
    {
        $seo->set(
            'Onde comprar — Revendedores TMAC',
            'Encontre a loja mais próxima que revende produtos TMAC. Busque pelo seu endereço ou use sua localização.',
            canonical: route('site.resellers'),
        );

        return view('site.resellers', [
            'states' => State::orderBy('name')->get(['id', 'uf', 'name']),
            'total'  => Reseller::active()->geocoded()->count(),
        ]);
    }

    /**
     * API de busca — retorna revendedores próximos.
     * GET /api/revendedores?lat=&lng=&raio=&uf=&cidade=&q=
     */
    public function search(Request $request): JsonResponse
    {
        $lat    = $request->float('lat') ?: null;
        $lng    = $request->float('lng') ?: null;
        $radius = $request->float('raio') ?: null;
        $uf     = $request->string('uf')->toString();
        $city   = $request->string('cidade')->toString();
        $term   = $request->string('q')->toString();

        $query = Reseller::with('state:id,uf,name')
            ->active()
            ->geocoded();

        if ($uf) {
            $query->whereHas('state', fn ($s) => $s->where('uf', strtoupper($uf)));
        }

        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }

        if ($term) {
            $query->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")
                  ->orWhere('company_name', 'like', "%{$term}%")
                  ->orWhere('neighborhood', 'like', "%{$term}%")
                  ->orWhere('city', 'like', "%{$term}%");
            });
        }

        if ($lat && $lng) {
            $query->nearby($lat, $lng, $radius);
        } else {
            $query->orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name');
        }

        $items = $query->limit(300)->get()->map(fn (Reseller $r) => [
            'id'        => $r->id,
            'name'      => $r->name,
            'city'      => $r->city,
            'uf'        => $r->state?->uf,
            'address'   => $r->full_address,
            'lat'       => (float) $r->latitude,
            'lng'       => (float) $r->longitude,
            'phone'     => $r->phone,
            'whatsapp'  => $r->whatsapp_url,
            'instagram' => $r->instagram,
            'website'   => $r->website,
            'hours'     => $r->opening_hours,
            'featured'  => (bool) $r->is_featured,
            'maps'      => $r->maps_url,
            'distance'  => isset($r->distance_km) ? round((float) $r->distance_km, 1) : null,
        ]);

        return response()->json([
            'ok'    => true,
            'count' => $items->count(),
            'items' => $items,
        ]);
    }

    /**
     * Geocodifica um endereço/CEP digitado pelo usuário (Nominatim / OpenStreetMap).
     * GET /api/geocode?q=Rua+Tal,+São+Paulo
     */
    public function geocode(Request $request): JsonResponse
    {
        $q = trim($request->string('q')->toString());

        if (mb_strlen($q) < 3) {
            return response()->json(['ok' => false, 'message' => 'Busca muito curta.'], 422);
        }

        // CEP puro → usa ViaCEP (mais preciso no Brasil)
        $digits = preg_replace('/\D/', '', $q);
        if (strlen($digits) === 8) {
            try {
                $via = Http::timeout(8)->get("https://viacep.com.br/ws/{$digits}/json/")->json();
                if (! empty($via) && empty($via['erro'])) {
                    $q = implode(', ', array_filter([
                        $via['logradouro'] ?? null,
                        $via['bairro'] ?? null,
                        $via['localidade'] ?? null,
                        $via['uf'] ?? null,
                    ]));
                }
            } catch (\Throwable $e) {
                // segue para o Nominatim com o texto original
            }
        }

        try {
            $res = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'TMAC-Import/1.0 (contato@tmacimport.com.br)'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'              => $q.', Brasil',
                    'format'         => 'json',
                    'limit'          => 1,
                    'countrycodes'   => 'br',
                    'addressdetails' => 1,
                ])
                ->json();

            if (empty($res)) {
                return response()->json(['ok' => false, 'message' => 'Endereço não encontrado.'], 404);
            }

            return response()->json([
                'ok'    => true,
                'lat'   => (float) $res[0]['lat'],
                'lng'   => (float) $res[0]['lon'],
                'label' => $res[0]['display_name'] ?? $q,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Falha ao localizar endereço.'], 500);
        }
    }
}

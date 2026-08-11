<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Representative;
use App\Models\State;
use App\Services\SeoMeta;
use Illuminate\Http\Request;

class RepresentativeController extends Controller
{
    public function index(Request $request, SeoMeta $seo)
    {
        $uf = $request->string('uf')->toString();

        $representatives = Representative::with('states')->active()
            ->when($uf, fn ($q) => $q->whereHas('states', fn ($s) => $s->where('uf', strtoupper($uf))))
            ->orderBy('name')
            ->get();

        $seo->set('Representantes — TMAC Import', 'Encontre o representante TMAC do seu estado.');

        return view('site.representatives', [
            'representatives' => $representatives,
            'states' => State::orderBy('uf')->get(),
            'uf' => strtoupper($uf),
        ]);
    }
}

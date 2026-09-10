<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Services\SeoMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(SeoMeta $seo)
    {
        if (Auth::guard('cliente')->check()) {
            return redirect()->route('cliente.tabela');
        }

        $seo->set('Área do Cliente', 'Acesse a Tabela de Vendas TMAC — consulte preços por estado e monte seu pedido.');

        return view('cliente.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::guard('cliente')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'E-mail ou senha inválidos.',
            ]);
        }

        $cliente = Auth::guard('cliente')->user();

        if (! $cliente->is_active) {
            Auth::guard('cliente')->logout();

            throw ValidationException::withMessages([
                'email' => 'Este acesso está desativado. Fale com o setor comercial da TMAC.',
            ]);
        }

        $request->session()->regenerate();
        $cliente->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('cliente.tabela'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('cliente')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('cliente.login');
    }
}

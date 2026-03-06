<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSuspended
{
    /**
     * Verifica se o usuário autenticado está suspenso.
     * Se estiver, faz logout e redireciona para o login.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->suspended_at) {
            $reason = Auth::user()->suspension_reason;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Sua conta foi suspensa.';
            if ($reason) {
                $message .= " Motivo: {$reason}";
            }
            $message .= ' Entre em contato com o suporte.';

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}

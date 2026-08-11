<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Cooldown por endereço de e-mail (não por conta): o throttle da rota é por
        // user id, o que permite bombing ilimitado registrando uma conta com o
        // e-mail da vítima. Falha em silêncio — não revelar o bloqueio evita dar
        // sinal ao atacante, e para o usuário legítimo o comportamento é idêntico.
        RateLimiter::attempt(
            'verify-email-send:'.Str::lower($request->user()->email),
            1,
            fn () => $request->user()->sendEmailVerificationNotification(),
            300,
        );

        return back()->with('status', 'verification-link-sent');
    }
}

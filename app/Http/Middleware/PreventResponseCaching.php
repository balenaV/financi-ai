<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventResponseCaching
{
    /**
     * O Laravel já marca respostas com sessão como "no-cache, private", mas isso
     * não basta para impedir o back-forward cache do navegador: depois de logout,
     * o botão "voltar" pode reexibir a página autenticada (ex.: landing com "Abrir
     * painel", ou o próprio dashboard) direto da memória, sem passar pelo servidor.
     * "no-store" é o sinal que os navegadores respeitam para desativar isso.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}

<?php

use App\Http\Middleware\AuditUserAction;
use App\Http\Middleware\EnsureRegistrationEnabled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // O container só é alcançável via a Nginx do host, publicada em 127.0.0.1
        // (ver CLAUDE.md) — quem conecta de fato é sempre um IP de rede privada
        // (loopback ou a rede do Docker), nunca a internet direto. Confiar em '*'
        // fazia o app aceitar X-Forwarded-For/Host de QUALQUER requisição, inclusive
        // uma vinda da internet — isso zera o rate limit de login (a chave usa
        // $request->ip(), forjável trocando o header a cada tentativa) e abre
        // brecha pra envenenar o host usado em links absolutos (ex.: reset de senha)
        // caso a Nginx do host não sobrescreva X-Forwarded-Host.
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '::1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
        ]);

        $middleware->alias([
            'audit' => AuditUserAction::class,
            'registration.enabled' => EnsureRegistrationEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

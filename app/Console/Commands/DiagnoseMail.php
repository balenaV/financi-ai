<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DiagnoseMail extends Command
{
    protected $signature = 'mail:diagnose {--send= : Endereço que receberá um e-mail de teste}';

    protected $description = 'Valida a configuração de e-mail sem exibir credenciais';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $this->line('Mailer: '.$mailer);
        $this->line('Remetente: '.config('mail.from.address'));

        if ($mailer === 'log') {
            $this->warn('O driver log não envia mensagens: ele grava o conteúdo em storage/logs.');

            return $this->option('send') ? self::FAILURE : self::SUCCESS;
        }

        if ($mailer === 'smtp') {
            $smtp = config('mail.mailers.smtp');
            $missing = collect(['host', 'port', 'username', 'password'])
                ->filter(fn ($key) => blank($smtp[$key] ?? null));

            if ($missing->isNotEmpty()) {
                $this->error('Configuração SMTP incompleta: '.$missing->implode(', ').'.');

                return self::FAILURE;
            }

            $scheme = $smtp['scheme'] ?? null;

            if (filled($scheme) && ! in_array($scheme, ['smtp', 'smtps'], true)) {
                $this->error("Esquema SMTP inválido: {$scheme}. Use smtp ou smtps.");

                return self::FAILURE;
            }

            $this->line("SMTP: {$smtp['host']}:{$smtp['port']}");
            $this->line('Esquema SMTP: '.($scheme ?: 'automático'));
            $this->line('Usuário SMTP: configurado');
            $this->line('Senha SMTP: configurada');
        }

        $recipient = $this->option('send');
        if (! $recipient) {
            $this->info('Configuração básica válida. Use --send=email@exemplo.com para testar a entrega.');

            return self::SUCCESS;
        }

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('Destinatário inválido.');

            return self::FAILURE;
        }

        try {
            Mail::raw('Este é um teste de entrega do financi.ai.', function ($message) use ($recipient) {
                $message->to($recipient)->subject('Teste de e-mail — financi.ai');
            });
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Falha no envio: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Mensagem aceita pelo servidor de e-mail para {$recipient}.");

        return self::SUCCESS;
    }
}

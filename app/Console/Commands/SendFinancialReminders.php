<?php

namespace App\Console\Commands;

use App\Enums\CreditCardBillStatus;
use App\Enums\DebtInstallmentStatus;
use App\Enums\TransactionStatus;
use App\Models\User;
use App\Notifications\FinancialReminder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class SendFinancialReminders extends Command
{
    protected $signature = 'finance:send-reminders {--days=7 : Janela de vencimentos em dias}';

    protected $description = 'Cria lembretes de vencimentos financeiros para cada usuário';

    public function handle(): int
    {
        $days = max(0, min(30, (int) $this->option('days')));
        $created = 0;

        User::query()->chunkById(100, function ($users) use ($days, &$created) {
            foreach ($users as $user) {
                $from = today();
                $until = today()->addDays($days);

                $user->debtInstallments()
                    ->with('debt')
                    ->whereIn('status', [DebtInstallmentStatus::Pending, DebtInstallmentStatus::Overdue])
                    ->whereBetween('due_date', [$from, $until])
                    ->each(function ($installment) use ($user, &$created) {
                        $created += $this->notifyOnce(
                            $user,
                            $installment,
                            'loan-installment',
                            "Parcela {$installment->number} de {$installment->debt->name}",
                            'Vence em '.$installment->due_date->format('d/m/Y').' · R$ '.number_format((float) $installment->amount, 2, ',', '.'),
                            route('debts.show', $installment->debt, absolute: false),
                        );
                    });

                $user->creditCardBills()
                    ->with('creditCard')
                    ->whereIn('status', [CreditCardBillStatus::Pending, CreditCardBillStatus::Overdue])
                    ->whereBetween('due_date', [$from, $until])
                    ->each(function ($bill) use ($user, &$created) {
                        $created += $this->notifyOnce(
                            $user,
                            $bill,
                            'credit-card-bill',
                            "Fatura de {$bill->creditCard->name}",
                            'Vence em '.$bill->due_date->format('d/m/Y').' · R$ '.number_format((float) $bill->total_amount, 2, ',', '.'),
                            route('credit-cards.show', $bill->creditCard, absolute: false),
                        );
                    });

                $user->transactions()
                    ->where('status', TransactionStatus::Planned)
                    ->whereNotNull('due_date')
                    ->whereBetween('due_date', [$from, $until])
                    ->each(function ($transaction) use ($user, &$created) {
                        $created += $this->notifyOnce(
                            $user,
                            $transaction,
                            'planned-transaction',
                            $transaction->description,
                            'Vence em '.$transaction->due_date->format('d/m/Y').' · R$ '.number_format((float) $transaction->amount, 2, ',', '.'),
                            route('dashboard', absolute: false),
                        );
                    });
            }
        });

        $this->info("{$created} novo(s) lembrete(s) criado(s).");

        return self::SUCCESS;
    }

    private function notifyOnce(User $user, Model $model, string $kind, string $title, string $message, string $url): int
    {
        $fingerprint = "{$kind}:{$model->getKey()}:{$model->due_date->toDateString()}";
        $exists = $user->notifications()
            ->whereRaw('CAST(data AS TEXT) LIKE ?', ['%"fingerprint":"'.$fingerprint.'"%'])
            ->exists();

        if ($exists) {
            return 0;
        }

        $user->notify(new FinancialReminder(
            $fingerprint,
            $title,
            $message,
            $url,
            $model->due_date->toDateString(),
        ));

        return 1;
    }
}

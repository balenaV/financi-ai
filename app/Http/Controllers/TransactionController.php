<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\Csv;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['start_date', 'end_date', 'type', 'status', 'category_id', 'account_id', 'min_amount', 'max_amount', 'search', 'sort']);
        $query = $request->user()->transactions()->with([
            'account', 'destinationAccount', 'category', 'creditCard', 'creditCardBill',
        ]);

        $query
            ->when($filters['start_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('competence_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn (Builder $q, $date) => $q->whereDate('competence_date', '<=', $date))
            ->when($filters['type'] ?? null, fn (Builder $q, $type) => $q->where('type', $type))
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $id) => $q->where('category_id', $id))
            ->when($filters['account_id'] ?? null, fn (Builder $q, $id) => $q->where(fn ($inner) => $inner->where('account_id', $id)->orWhere('destination_account_id', $id)))
            ->when($filters['min_amount'] ?? null, fn (Builder $q, $value) => $q->where('amount', '>=', $value))
            ->when($filters['max_amount'] ?? null, fn (Builder $q, $value) => $q->where('amount', '<=', $value))
            ->when($filters['search'] ?? null, fn (Builder $q, $search) => $q->whereRaw('LOWER(description) LIKE ?', ['%'.mb_strtolower($search).'%']));

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->oldest('competence_date'),
            'amount_desc' => $query->orderByDesc('amount'),
            'amount_asc' => $query->orderBy('amount'),
            default => $query->latest('competence_date')->latest('id'),
        };

        return view('transactions.index', [
            'transactions' => $query->paginate(20)->withQueryString(),
            'accounts' => $request->user()->accounts()->orderBy('name')->get(),
            'categories' => $request->user()->categories()->orderBy('name')->get(),
            'filters' => $filters,
            'types' => TransactionType::cases(),
            'statuses' => TransactionStatus::cases(),
        ]);
    }

    public function store(TransactionRequest $request, TransactionService $service): RedirectResponse
    {
        $created = $service->create($request->user(), $request->validated());

        return redirect(route('dashboard').'#transacoes')->with('success', $created->count() > 1
            ? "{$created->count()} transações criadas com sucesso."
            : 'Transação criada com sucesso.');
    }

    public function update(TransactionRequest $request, Transaction $transaction, TransactionService $service): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $service->update($transaction, $request->validated(), $request->boolean('update_future'));

        return redirect(route('dashboard').'#transacoes')->with('success', 'Transação atualizada.');
    }

    public function duplicate(Request $request, Transaction $transaction, TransactionService $service): RedirectResponse
    {
        $this->authorize('view', $transaction);
        $service->create($request->user(), [
            ...$transaction->only(['account_id', 'destination_account_id', 'category_id', 'description', 'amount', 'notes']),
            'type' => $transaction->type->value,
            'competence_date' => today()->toDateString(),
            'due_date' => today()->toDateString(),
            'paid_at' => null,
            'status' => TransactionStatus::Planned->value,
            'payment_mode' => 'single',
        ]);

        return back()->with('success', 'Transação duplicada como planejada.');
    }

    public function cancel(Request $request, Transaction $transaction, TransactionService $service): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $service->cancel($transaction, $request->boolean('future'));

        return back()->with('success', 'Transação cancelada.');
    }

    public function destroy(Transaction $transaction, TransactionService $service): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $service->delete($transaction);

        return back()->with('success', 'Transação excluída.');
    }

    public function export(Request $request): StreamedResponse
    {
        $transactions = $request->user()->transactions()->with(['account', 'destinationAccount', 'category', 'creditCard'])
            ->when($request->start_date, fn ($q, $date) => $q->whereDate('competence_date', '>=', $date))
            ->when($request->end_date, fn ($q, $date) => $q->whereDate('competence_date', '<=', $date))
            ->orderBy('competence_date')->get();

        return response()->streamDownload(function () use ($transactions) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Data', 'Tipo', 'Descrição', 'Conta', 'Categoria', 'Status', 'Valor'], ';');
            foreach ($transactions as $transaction) {
                fputcsv($output, [
                    $transaction->competence_date->format('d/m/Y'),
                    $transaction->type->label(),
                    Csv::safe($transaction->description),
                    Csv::safe($transaction->account?->name ?? $transaction->creditCard?->name ?? 'Sem conta'),
                    Csv::safe($transaction->category?->name ?? ''),
                    $transaction->status->label(),
                    str_replace('.', ',', $transaction->amount),
                ], ';');
            }
            fclose($output);
        }, 'transacoes-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportOfx(Request $request): StreamedResponse
    {
        $transactions = $request->user()->transactions()->with('account')
            ->when($request->start_date, fn ($q, $date) => $q->whereDate('competence_date', '>=', $date))
            ->when($request->end_date, fn ($q, $date) => $q->whereDate('competence_date', '<=', $date))
            ->orderBy('competence_date')->get();

        $start = $transactions->min('competence_date')?->format('Ymd') ?? today()->format('Ymd');
        $end = $transactions->max('competence_date')?->format('Ymd') ?? today()->format('Ymd');

        return response()->streamDownload(function () use ($transactions, $start, $end) {
            echo "OFXHEADER:100\r\nDATA:OFXSGML\r\nVERSION:102\r\nSECURITY:NONE\r\nENCODING:UTF-8\r\nCHARSET:NONE\r\nCOMPRESSION:NONE\r\nOLDFILEUID:NONE\r\nNEWFILEUID:NONE\r\n\r\n";
            echo "<OFX><BANKMSGSRSV1><STMTTRNRS><STMTRS><CURDEF>BRL<BANKTRANLIST><DTSTART>{$start}<DTEND>{$end}\n";
            foreach ($transactions as $transaction) {
                $amount = $transaction->type->value === 'expense' ? '-'.$transaction->amount : $transaction->amount;
                $memo = $this->ofxSafe($transaction->description);
                echo '<STMTTRN><TRNTYPE>'.($transaction->type->value === 'expense' ? 'DEBIT' : 'CREDIT')
                    ."<DTPOSTED>{$transaction->competence_date->format('Ymd')}<TRNAMT>{$amount}<FITID>{$transaction->id}<MEMO>{$memo}</STMTTRN>\n";
            }
            echo '</BANKTRANLIST></STMTRS></STMTTRNRS></BANKMSGSRSV1></OFX>';
        }, 'transacoes-'.today()->format('Y-m-d').'.ofx', ['Content-Type' => 'application/x-ofx']);
    }

    private function ofxSafe(string $value): string
    {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $value);
    }
}

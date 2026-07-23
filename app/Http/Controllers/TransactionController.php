<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
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

    public function create(Request $request): View
    {
        return $this->form($request, new Transaction);
    }

    public function store(TransactionRequest $request, TransactionService $service): RedirectResponse
    {
        $created = $service->create($request->user(), $request->validated());

        return to_route('transactions.index')->with('success', $created->count() > 1
            ? "{$created->count()} transações criadas com sucesso."
            : 'Transação criada com sucesso.');
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        return $this->form($request, $transaction);
    }

    public function update(TransactionRequest $request, Transaction $transaction, TransactionService $service): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $service->update($transaction, $request->validated(), $request->boolean('update_future'));

        return to_route('transactions.index')->with('success', 'Transação atualizada.');
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
                    $this->csvSafe($transaction->description),
                    $this->csvSafe($transaction->account?->name ?? $transaction->creditCard?->name ?? 'Sem conta'),
                    $this->csvSafe($transaction->category?->name ?? ''),
                    $transaction->status->label(),
                    str_replace('.', ',', $transaction->amount),
                ], ';');
            }
            fclose($output);
        }, 'transacoes-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function form(Request $request, Transaction $transaction): View
    {
        return view('transactions.form', [
            'transaction' => $transaction,
            'accounts' => $request->user()->accounts()->active()->orderBy('name')->get(),
            'creditCards' => $request->user()->creditCards()->where('active', true)->orderBy('name')->get(),
            'categories' => $request->user()->categories()->where('active', true)->orderBy('name')->get(),
            'types' => TransactionType::cases(),
            'statuses' => TransactionStatus::cases(),
        ]);
    }

    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}

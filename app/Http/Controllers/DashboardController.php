<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard, TransactionService $transactions): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'account_id' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'edit_account' => ['nullable', 'integer'],
            'edit_card' => ['nullable', 'integer'],
            'edit_transaction' => ['nullable', 'integer'],
            'tx_search' => ['nullable', 'string', 'max:255'],
            'tx_type' => ['nullable', 'string', 'in:income,expense,pending'],
            'tx_category' => ['nullable', 'integer'],
            'tx_account' => ['nullable', 'integer'],
            'tx_status' => ['nullable', 'string', 'in:completed,pending,cancelled'],
            'tx_page' => ['nullable', 'integer', 'min:1'],
        ]);

        if (isset($filters['account_id'])) {
            $request->user()->accounts()->findOrFail($filters['account_id']);
        }

        $editAccount = isset($filters['edit_account'])
            ? $request->user()->accounts()->find($filters['edit_account'])
            : null;
        $editCard = isset($filters['edit_card'])
            ? $request->user()->creditCards()->find($filters['edit_card'])
            : null;
        $editTransaction = isset($filters['edit_transaction'])
            ? $request->user()->transactions()->find($filters['edit_transaction'])
            : null;

        $data = $dashboard->build($request->user(), $filters);

        $pendingStatuses = ['planned', 'overdue'];
        $txStatus = match (true) {
            ($filters['tx_status'] ?? null) === 'completed' => 'completed',
            ($filters['tx_status'] ?? null) === 'cancelled' => 'cancelled',
            ($filters['tx_status'] ?? null) === 'pending' => $pendingStatuses,
            ($filters['tx_type'] ?? null) === 'pending' => $pendingStatuses,
            default => null,
        };
        $transactionsPage = $transactions->filtered($request->user(), [
            'start_date' => $data['period']['start']->toDateString(),
            'end_date' => $data['period']['end']->toDateString(),
            'search' => $filters['tx_search'] ?? null,
            'type' => in_array($filters['tx_type'] ?? null, ['income', 'expense'], true) ? $filters['tx_type'] : null,
            'status' => $txStatus,
            'category_id' => $filters['tx_category'] ?? null,
            'account_id' => $filters['tx_account'] ?? null,
        ], perPage: 8, pageName: 'tx_page');

        return view('dashboard', [
            'dashboard' => $data,
            'filters' => $filters,
            'editAccount' => $editAccount,
            'editCard' => $editCard,
            'editTransaction' => $editTransaction,
            'transactionsPage' => $transactionsPage,
            'categories' => $request->user()->categories()->where('active', true)->orderBy('name')->get(),
            'accountTypeTiles' => [
                ['key' => 'corrente', 'type' => 'checking', 'icon' => 'bank', 'iconClass' => 'fa-solid fa-building-columns', 'label' => 'Conta corrente'],
                ['key' => 'poupanca', 'type' => 'savings', 'icon' => 'bank', 'iconClass' => 'fa-solid fa-piggy-bank', 'label' => 'Poupança'],
                ['key' => 'carteira', 'type' => 'cash', 'icon' => 'cash', 'iconClass' => 'fa-solid fa-wallet', 'label' => 'Dinheiro em espécie'],
                ['key' => 'investimento', 'type' => 'investment', 'icon' => 'chart', 'iconClass' => 'fa-solid fa-chart-line', 'label' => 'Investimento'],
                ['key' => 'outra', 'type' => 'other', 'icon' => 'wallet', 'iconClass' => 'fa-solid fa-circle-dot', 'label' => 'Outra'],
            ],
        ]);
    }
}

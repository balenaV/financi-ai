<?php

namespace App\Http\Controllers;

use App\Services\BudgetService;
use App\Services\DebtService;
use App\Services\FinancialReportService;
use App\Services\InvestmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const TYPES = [
        'cash_flow' => 'Fluxo de caixa',
        'income_categories' => 'Receitas por categoria',
        'expense_categories' => 'Despesas por categoria',
        'net_worth' => 'Evolução patrimonial',
        'debts' => 'Evolução de dívidas',
        'investments' => 'Evolução de investimentos',
        'budgets' => 'Orçado versus realizado',
        'accounts' => 'Movimentações por conta',
    ];

    public function index(
        Request $request,
        FinancialReportService $reports,
        DebtService $debts,
        InvestmentService $investments,
        BudgetService $budgets,
    ): View {
        $filters = $request->validate([
            'type' => ['nullable', 'in:'.implode(',', array_keys(self::TYPES))],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'account_id' => ['nullable', 'integer'],
        ]);
        $type = $filters['type'] ?? 'cash_flow';

        if (isset($filters['account_id'])) {
            $request->user()->accounts()->findOrFail($filters['account_id']);
        }

        $transactions = $reports->transactions($request->user(), $filters);
        $summary = $reports->summarize($transactions);
        $groups = $type === 'income_categories' ? $summary['by_income_category'] : $summary['by_expense_category'];
        $categoryChart = [
            'type' => 'doughnut',
            'labels' => $groups->keys()->values(),
            'datasets' => [['data' => $groups->values()]],
        ];

        return view('reports.index', [
            'types' => self::TYPES,
            'type' => $type,
            'filters' => $filters,
            'transactions' => $transactions,
            'summary' => $summary,
            'categoryChart' => $categoryChart,
            'accounts' => $request->user()->accounts()->orderBy('name')->get(),
            'debtRows' => $type === 'debts'
                ? $request->user()->debts()->with('installments')->get()->map(fn ($debt) => ['model' => $debt, 'metrics' => $debts->summary($debt)])
                : collect(),
            'investmentRows' => $type === 'investments'
                ? $request->user()->investments()->get()->map(fn ($investment) => ['model' => $investment, 'metrics' => $investments->metrics($investment)])
                : collect(),
            'budgetRows' => $type === 'budgets'
                ? $request->user()->budgets()->with(['category', 'user'])->get()->map(fn ($budget) => ['model' => $budget, 'metrics' => $budgets->metrics($budget)])
                : collect(),
        ]);
    }

    public function export(Request $request, FinancialReportService $reports): StreamedResponse
    {
        $transactions = $reports->transactions($request->user(), $request->only(['start_date', 'end_date', 'account_id', 'type']));

        return response()->streamDownload(function () use ($transactions) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Data', 'Descrição', 'Tipo', 'Categoria', 'Conta', 'Status', 'Valor'], ';');
            foreach ($transactions as $transaction) {
                fputcsv($output, [
                    $transaction->competence_date->format('d/m/Y'),
                    $transaction->description,
                    $transaction->type->label(),
                    $transaction->category?->name,
                    $transaction->account?->name ?? $transaction->creditCard?->name ?? 'Sem conta',
                    $transaction->status->label(),
                    str_replace('.', ',', $transaction->amount),
                ], ';');
            }
            fclose($output);
        }, 'relatorio-financeiro-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

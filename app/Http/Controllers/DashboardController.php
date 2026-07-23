<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): View
    {
        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'account_id' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if (isset($filters['account_id'])) {
            $request->user()->accounts()->findOrFail($filters['account_id']);
        }

        $data = $dashboard->build($request->user(), $filters);
        $chartConfigs = [
            'cashflow' => [
                'type' => 'bar',
                'labels' => $data['charts']['labels'],
                'datasets' => [
                    ['label' => 'Receitas', 'data' => $data['charts']['income'], 'backgroundColor' => '#1d9e75'],
                    ['label' => 'Despesas', 'data' => $data['charts']['expense'], 'backgroundColor' => '#dc2626'],
                ],
            ],
            'balance' => [
                'type' => 'line',
                'labels' => $data['charts']['labels'],
                'datasets' => [[
                    'label' => 'Saldo',
                    'data' => $data['charts']['balances'],
                    'borderColor' => '#534ab7',
                    'backgroundColor' => '#eeedfe',
                    'fill' => true,
                    'tension' => 0.35,
                ]],
            ],
            'categories' => [
                'type' => 'doughnut',
                'labels' => $data['charts']['expense_categories']['labels'],
                'datasets' => [['data' => $data['charts']['expense_categories']['values']]],
            ],
            'investments' => [
                'type' => 'doughnut',
                'labels' => $data['charts']['investments']['labels'],
                'datasets' => [['data' => $data['charts']['investments']['values']]],
            ],
        ];

        return view('dashboard', [
            'dashboard' => $data,
            'chartConfigs' => $chartConfigs,
            'filterAccounts' => $request->user()->accounts()->active()->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }
}

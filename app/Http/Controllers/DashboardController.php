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
            'edit_account' => ['nullable', 'integer'],
            'edit_card' => ['nullable', 'integer'],
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

        $data = $dashboard->build($request->user(), $filters);

        return view('dashboard', [
            'dashboard' => $data,
            'filters' => $filters,
            'editAccount' => $editAccount,
            'editCard' => $editCard,
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

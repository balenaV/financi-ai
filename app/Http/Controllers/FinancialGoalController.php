<?php

namespace App\Http\Controllers;

use App\Enums\GoalStatus;
use App\Http\Requests\FinancialGoalRequest;
use App\Models\FinancialGoal;
use App\Services\AccountBalanceService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialGoalController extends Controller
{
    public function index(Request $request, AccountBalanceService $balances): View
    {
        $goals = $request->user()->financialGoals()->with('account')->latest()->paginate(12);
        $goals->setCollection($goals->getCollection()->map(function (FinancialGoal $goal) use ($balances) {
            $current = $goal->use_account_balance && $goal->account
                ? $balances->current($goal->account)
                : $goal->current_amount;

            return [
                'goal' => $goal,
                'current' => $current,
                'remaining' => bcsub($goal->target_amount, $current, 2),
                'percentage' => Money::percentage($current, $goal->target_amount),
            ];
        }));

        return view('goals.index', compact('goals'));
    }

    public function create(Request $request): View
    {
        return view('goals.form', [
            'goal' => new FinancialGoal,
            'accounts' => $request->user()->accounts()->active()->orderBy('name')->get(),
            'statuses' => GoalStatus::cases(),
        ]);
    }

    public function store(FinancialGoalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['target_amount'] = Money::normalize($data['target_amount']);
        $data['current_amount'] = Money::normalize($data['current_amount']);
        $data['use_account_balance'] = $request->boolean('use_account_balance');
        $request->user()->financialGoals()->create($data);

        return to_route('goals.index')->with('success', 'Meta criada.');
    }

    public function edit(Request $request, FinancialGoal $goal): View
    {
        $this->authorize('update', $goal);

        return view('goals.form', [
            'goal' => $goal,
            'accounts' => $request->user()->accounts()->active()->orderBy('name')->get(),
            'statuses' => GoalStatus::cases(),
        ]);
    }

    public function update(FinancialGoalRequest $request, FinancialGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        $data = $request->validated();
        $data['target_amount'] = Money::normalize($data['target_amount']);
        $data['current_amount'] = Money::normalize($data['current_amount']);
        $data['use_account_balance'] = $request->boolean('use_account_balance');
        $goal->update($data);

        return to_route('goals.index')->with('success', 'Meta atualizada.');
    }

    public function destroy(FinancialGoal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);
        $goal->delete();

        return back()->with('success', 'Meta excluída.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialGoalRequest;
use App\Models\FinancialGoal;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialGoalController extends Controller
{
    public function store(FinancialGoalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['target_amount'] = Money::normalize($data['target_amount']);
        $data['current_amount'] = Money::normalize($data['current_amount']);
        $data['use_account_balance'] = $request->boolean('use_account_balance');
        $request->user()->financialGoals()->create($data);

        return redirect(route('dashboard').'#metas')->with('success', 'Meta criada.');
    }

    public function update(FinancialGoalRequest $request, FinancialGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        $data = $request->validated();
        $data['target_amount'] = Money::normalize($data['target_amount']);
        $data['current_amount'] = Money::normalize($data['current_amount']);
        $data['use_account_balance'] = $request->boolean('use_account_balance');
        $goal->update($data);

        return redirect(route('dashboard').'#metas')->with('success', 'Meta atualizada.');
    }

    public function destroy(FinancialGoal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);
        $goal->delete();

        return redirect(route('dashboard').'#metas')->with('success', 'Meta excluída.');
    }

    /**
     * Aporte simplificado: soma direto no progresso da meta. Sem lançamento
     * nem conta de origem — diferente dos investimentos, a meta não tem um
     * livro-razão de operações, só um valor acumulado.
     */
    public function contribute(Request $request, FinancialGoal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        $data = $request->validate([
            'amount' => ['required', 'max:15', 'regex:/^[\d.,\s]+$/'],
        ]);
        $amount = Money::normalize($data['amount']);

        DB::transaction(function () use ($request, $goal, $amount) {
            $goal->update(['current_amount' => bcadd((string) $goal->current_amount, $amount, 2)]);
            $request->user()->goalContributions()->create([
                'financial_goal_id' => $goal->id,
                'amount' => $amount,
                'contributed_at' => today(),
            ]);
        });

        return redirect(route('dashboard').'#metas')->with('success', 'Aporte registrado.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Services\BudgetService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request, BudgetService $service): View
    {
        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);
        $budgets = $request->user()->budgets()->with(['category', 'user'])
            ->where('month', $month)->where('year', $year)->get()
            ->map(fn (Budget $budget) => ['budget' => $budget, 'metrics' => $service->metrics($budget)]);

        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => $request->user()->categories()->where('type', CategoryType::Expense->value)->where('active', true)->orderBy('name')->get(),
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function store(BudgetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['limit_amount'] = Money::normalize($data['limit_amount']);
        $request->user()->budgets()->create($data);

        return back()->with('success', 'Orçamento criado.');
    }

    public function edit(Budget $budget): View
    {
        $this->authorize('update', $budget);

        return view('budgets.form', [
            'budget' => $budget,
            'categories' => $budget->user->categories()->where('type', CategoryType::Expense->value)->orderBy('name')->get(),
        ]);
    }

    public function update(BudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);
        $data = $request->validated();
        $data['limit_amount'] = Money::normalize($data['limit_amount']);
        $budget->update($data);

        return redirect(route('dashboard').'#orcamentos')->with('success', 'Orçamento atualizado.');
    }

    public function copy(Request $request, BudgetService $service): RedirectResponse
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);
        $copied = $service->copyPreviousMonth($request->user(), $data['month'], $data['year']);

        return back()->with('success', "{$copied->count()} orçamentos copiados.");
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);
        $budget->delete();

        return back()->with('success', 'Orçamento excluído.');
    }
}

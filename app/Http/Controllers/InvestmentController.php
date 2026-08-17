<?php

namespace App\Http\Controllers;

use App\Enums\InvestmentOperationType;
use App\Http\Requests\InvestmentOperationRequest;
use App\Http\Requests\InvestmentRequest;
use App\Models\Investment;
use App\Services\InvestmentService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function store(InvestmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['invested_amount'] = Money::normalize($data['invested_amount']);
        $data['current_amount'] = Money::normalize($data['current_amount']);
        $request->user()->investments()->create($data);

        return redirect(route('dashboard').'#investimentos')->with('success', 'Investimento criado.');
    }

    public function show(Request $request, Investment $investment, InvestmentService $service): View
    {
        $this->authorize('view', $investment);

        return view('investments.show', [
            'investment' => $investment->load(['operations.account']),
            'metrics' => $service->metrics($investment),
            'operationTypes' => InvestmentOperationType::cases(),
            'accounts' => $request->user()->accounts()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(InvestmentRequest $request, Investment $investment): RedirectResponse
    {
        $this->authorize('update', $investment);
        $data = $request->validated();
        $data['invested_amount'] = Money::normalize($data['invested_amount']);
        $data['current_amount'] = Money::normalize($data['current_amount']);
        $investment->update($data);

        return redirect(route('dashboard').'#investimentos')->with('success', 'Investimento atualizado.');
    }

    public function operation(
        InvestmentOperationRequest $request,
        Investment $investment,
        InvestmentService $service,
    ): RedirectResponse {
        $this->authorize('update', $investment);
        $service->addOperation($request->user(), $investment, $request->validated());

        return back()->with('success', 'Operação registrada sem duplicidade patrimonial.');
    }

    public function destroy(Investment $investment): RedirectResponse
    {
        $this->authorize('delete', $investment);

        if ($investment->operations()->exists()) {
            return back()->with('error', 'O investimento possui operações. Marque-o como encerrado ou resgatado.');
        }

        $investment->delete();

        return redirect(route('dashboard').'#investimentos')->with('success', 'Investimento excluído.');
    }
}

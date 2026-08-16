<?php

namespace App\Http\Controllers;

use App\Enums\InvestmentOperationType;
use App\Enums\InvestmentStatus;
use App\Enums\InvestmentType;
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
    public function index(Request $request, InvestmentService $service): View
    {
        $investments = $request->user()->investments()->with('operations')->latest()->paginate(12);
        $investments->setCollection($investments->getCollection()->map(fn (Investment $investment) => [
            'investment' => $investment,
            'metrics' => $service->metrics($investment),
        ]));

        return view('investments.index', compact('investments'));
    }

    public function create(): View
    {
        return view('investments.form', [
            'investment' => new Investment,
            'types' => InvestmentType::cases(),
            'statuses' => InvestmentStatus::cases(),
        ]);
    }

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

    public function edit(Investment $investment): View
    {
        $this->authorize('update', $investment);

        return view('investments.form', [
            'investment' => $investment,
            'types' => InvestmentType::cases(),
            'statuses' => InvestmentStatus::cases(),
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

        return to_route('investments.index')->with('success', 'Investimento excluído.');
    }
}

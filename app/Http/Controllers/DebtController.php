<?php

namespace App\Http\Controllers;

use App\Enums\DebtStatus;
use App\Http\Requests\DebtRequest;
use App\Models\Debt;
use App\Models\DebtInstallment;
use App\Services\CreditCardService;
use App\Services\DebtService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebtController extends Controller
{
    public function index(Request $request, DebtService $service, CreditCardService $creditCards): View
    {
        $debts = $request->user()->debts()->with('installments')->latest()->paginate(12);
        $debts->setCollection($debts->getCollection()->map(fn (Debt $debt) => [
            'debt' => $debt,
            'summary' => $service->summary($debt),
        ]));

        return view('debts.index', [
            'debts' => $debts,
            'debtSummary' => $creditCards->debtSummary($request->user()),
        ]);
    }

    public function create(): View
    {
        return view('debts.form', ['debt' => new Debt, 'statuses' => DebtStatus::cases()]);
    }

    public function store(DebtRequest $request, DebtService $service): RedirectResponse
    {
        $service->create($request->user(), $request->validated());

        return redirect(route('dashboard').'#dividas')->with('success', 'Dívida e parcelas criadas com sucesso.');
    }

    public function show(Debt $debt, DebtService $service): View
    {
        $this->authorize('view', $debt);

        return view('debts.show', [
            'debt' => $debt->load(['installments.transaction']),
            'summary' => $service->summary($debt),
            'accounts' => $debt->user->accounts()->active()->orderBy('name')->get(),
        ]);
    }

    public function edit(Debt $debt): View
    {
        $this->authorize('update', $debt);

        return view('debts.form', ['debt' => $debt, 'statuses' => DebtStatus::cases()]);
    }

    public function update(DebtRequest $request, Debt $debt): RedirectResponse
    {
        $this->authorize('update', $debt);
        $data = $request->validated();
        $data['original_amount'] = Money::normalize($data['original_amount']);
        $data['expected_total_amount'] = Money::normalize($data['expected_total_amount']);
        unset($data['first_due_date']);
        $debt->update($data);

        return redirect(route('dashboard').'#dividas')->with('success', 'Dívida atualizada.');
    }

    public function pay(Request $request, DebtInstallment $installment, DebtService $service): RedirectResponse
    {
        $this->authorize('update', $installment);
        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'paid_at' => ['nullable', 'date'],
        ]);
        $account = $request->user()->accounts()->findOrFail($data['account_id']);
        $service->pay($request->user(), $installment, $account, $data['paid_at'] ?? null);

        return back()->with('success', 'Parcela paga e despesa registrada.');
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        $this->authorize('delete', $debt);

        if ($debt->installments()->whereNotNull('transaction_id')->exists()) {
            return back()->with('error', 'A dívida possui pagamentos. Marque-a como cancelada em vez de excluir.');
        }

        $debt->delete();

        return to_route('debts.index')->with('success', 'Dívida excluída.');
    }
}

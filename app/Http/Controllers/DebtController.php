<?php

namespace App\Http\Controllers;

use App\Http\Requests\DebtRequest;
use App\Models\Debt;
use App\Models\DebtInstallment;
use App\Services\DebtService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function store(DebtRequest $request, DebtService $service): RedirectResponse
    {
        $service->create($request->user(), $request->validated());

        return redirect(route('dashboard').'#dividas')->with('success', 'Dívida e parcelas criadas com sucesso.');
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

        return redirect(route('dashboard').'#dividas')->with('success', 'Parcela paga e despesa registrada.');
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        $this->authorize('delete', $debt);

        if ($debt->installments()->whereNotNull('transaction_id')->exists()) {
            return redirect(route('dashboard').'#dividas')->with('error', 'A dívida possui pagamentos. Marque-a como cancelada em vez de excluir.');
        }

        $debt->delete();

        return redirect(route('dashboard').'#dividas')->with('success', 'Dívida excluída.');
    }
}

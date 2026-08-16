<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditCardRequest;
use App\Models\CreditCard;
use App\Services\CreditCardService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditCardController extends Controller
{
    public function index(Request $request, CreditCardService $service): View
    {
        $service->refreshOverdue($request->user());
        $cards = $request->user()->creditCards()
            ->with('bills')
            ->orderByDesc('active')
            ->orderBy('name')
            ->paginate(12);
        $summaries = $cards->getCollection()
            ->mapWithKeys(fn (CreditCard $card) => [$card->id => $service->cardSummary($card)]);

        return view('credit-cards.index', [
            'cards' => $cards,
            'summaries' => $summaries,
            'debtSummary' => $service->debtSummary($request->user()),
        ]);
    }

    public function create(): View
    {
        return view('credit-cards.form', ['creditCard' => new CreditCard]);
    }

    public function store(CreditCardRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['credit_limit'] = Money::normalize($data['credit_limit']);
        $data['active'] = $request->boolean('active');
        $request->user()->creditCards()->create($data);

        return redirect(route('dashboard').'#cartoes')->with('success', 'Cartão cadastrado.');
    }

    public function show(Request $request, CreditCard $creditCard, CreditCardService $service): View
    {
        $this->authorize('view', $creditCard);
        $service->refreshOverdue($request->user());

        $creditCard->load(['bills.transaction', 'bills.purchases']);

        return view('credit-cards.show', [
            'creditCard' => $creditCard,
            'summary' => $service->cardSummary($creditCard),
            'debtSummary' => $service->debtSummary($request->user()),
            'accounts' => $request->user()->accounts()->active()->orderBy('name')->get(),
            'editableBillIds' => $creditCard->bills->filter(fn ($bill) => $service->canEdit($bill))->pluck('id'),
        ]);
    }

    public function edit(CreditCard $creditCard): View
    {
        $this->authorize('update', $creditCard);

        return view('credit-cards.form', compact('creditCard'));
    }

    public function update(CreditCardRequest $request, CreditCard $creditCard): RedirectResponse
    {
        $this->authorize('update', $creditCard);
        $data = $request->validated();
        $data['credit_limit'] = Money::normalize($data['credit_limit']);
        $data['active'] = $request->boolean('active');
        $creditCard->update($data);

        return redirect(route('dashboard').'#cartoes')->with('success', 'Cartão atualizado.');
    }

    public function destroy(CreditCard $creditCard): RedirectResponse
    {
        $this->authorize('delete', $creditCard);

        if ($creditCard->bills()->exists()) {
            return back()->with('error', 'O cartão possui faturas e não pode ser excluído. Desative-o.');
        }

        $creditCard->delete();

        return to_route('credit-cards.index')->with('success', 'Cartão excluído.');
    }
}

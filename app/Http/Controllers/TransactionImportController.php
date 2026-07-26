<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Services\TransactionImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionImportController extends Controller
{
    public function create(Request $request): View
    {
        return view('transactions.import', [
            'accounts' => $request->user()->accounts()->active()->orderBy('name')->get(),
            'categories' => $request->user()->categories()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, TransactionImportService $service): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)->whereNull('deleted_at')),
            ],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)->whereNull('deleted_at')),
            ],
            'file' => ['required', 'file', 'max:5120', 'mimes:csv,txt,ofx'],
        ]);

        $account = Account::query()->findOrFail($validated['account_id']);
        $category = isset($validated['category_id']) ? Category::query()->findOrFail($validated['category_id']) : null;
        $result = $service->import($request->user(), $account, $request->file('file'), $category);

        return to_route('transactions.index')->with(
            'success',
            "{$result['created']} transações importadas; {$result['duplicates']} duplicadas ignoradas; {$result['invalid']} linhas inválidas.",
        );
    }
}

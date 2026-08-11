<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommitImportRequest;
use App\Http\Requests\PreviewImportRequest;
use App\Http\Requests\StoreImportRequest;
use App\Models\ImportBatch;
use App\Services\TransactionImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(StoreImportRequest $request, TransactionImportService $service): JsonResponse
    {
        $account = $request->user()->accounts()->findOrFail($request->validated('account_id'));
        $result = $service->createBatch($request->user(), $account, $request->file('file'));

        return response()->json([
            'batch_id' => $result['batch']->id,
            'format' => $result['batch']->format->value,
            'columns' => $result['columns'],
            'suggested_mapping' => $result['suggested_mapping'],
        ]);
    }

    public function preview(PreviewImportRequest $request, ImportBatch $batch, TransactionImportService $service): JsonResponse
    {
        $this->authorize('view', $batch);

        $service->dispatchParse(
            $batch,
            $request->input('column_map'),
            $request->input('date_format'),
            $request->input('decimal_separator'),
        );

        return response()->json(['status' => $batch->fresh()->status->value]);
    }

    public function show(ImportBatch $batch): JsonResponse
    {
        $this->authorize('view', $batch);

        $batch->refresh();

        return response()->json([
            'status' => $batch->status->value,
            'error' => $batch->error,
            'rows_read' => $batch->rows_read,
            'rows' => $batch->status->value === 'parsed' || $batch->status->value === 'committed'
                ? $batch->rows()->orderBy('posted_at')->orderBy('id')->get()->map(fn ($row) => [
                    'id' => $row->id,
                    'posted_at' => $row->posted_at->format('d/m'),
                    'description' => $row->description,
                    'type' => $row->type->value,
                    'amount' => $row->amount,
                    'status' => $row->status->value,
                    'status_label' => $row->status->label(),
                    'included' => $row->included,
                    'suggested_category_id' => $row->suggested_category_id,
                ])
                : [],
        ]);
    }

    public function commit(CommitImportRequest $request, ImportBatch $batch, TransactionImportService $service): JsonResponse
    {
        $this->authorize('update', $batch);

        $result = $service->commit($batch, $request->validated('row_ids'), $request->validated('categories', []));

        return response()->json($result);
    }

    public function revert(ImportBatch $batch, TransactionImportService $service): RedirectResponse
    {
        $this->authorize('update', $batch);

        $service->revert($batch);

        return to_route('transactions.index')->with('success', 'Importação desfeita — os lançamentos foram removidos.');
    }
}

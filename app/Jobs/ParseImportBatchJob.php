<?php

namespace App\Jobs;

use App\Enums\ImportBatchStatus;
use App\Models\ImportBatch;
use App\Services\TransactionImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ParseImportBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public readonly ImportBatch $importBatch,
        public readonly string $storedPath,
        public readonly ?array $columnMap = null,
        public readonly ?string $dateFormat = null,
        public readonly ?string $decimalSeparator = null,
    ) {}

    public function handle(TransactionImportService $service): void
    {
        try {
            $service->parseIntoStaging($this->importBatch, $this->storedPath, $this->columnMap, $this->dateFormat, $this->decimalSeparator);
        } finally {
            Storage::disk('local')->delete($this->storedPath);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Storage::disk('local')->delete($this->storedPath);

        $this->importBatch->update([
            'status' => ImportBatchStatus::Failed,
            'error' => 'Não foi possível ler o arquivo. Confira se o formato é válido.',
            'stored_path' => null,
        ]);
    }
}

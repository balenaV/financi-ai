<?php

namespace App\Console\Commands;

use App\Enums\ImportBatchStatus;
use App\Models\ImportBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneStaleImportBatches extends Command
{
    protected $signature = 'imports:prune-stale {--hours=24 : Idade máxima de um lote parado antes de expirar}';

    protected $description = 'Expira lotes de importação abandonados e apaga o arquivo temporário associado';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $cutoff = now()->subHours($hours);
        $pruned = 0;

        ImportBatch::query()
            ->whereIn('status', [ImportBatchStatus::Pending, ImportBatchStatus::Parsing])
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($batches) use (&$pruned) {
                foreach ($batches as $batch) {
                    if ($batch->stored_path) {
                        Storage::disk('local')->delete($batch->stored_path);
                    }

                    $batch->update([
                        'status' => ImportBatchStatus::Failed,
                        'error' => 'Importação expirada — envie o arquivo novamente.',
                        'stored_path' => null,
                    ]);
                    $pruned++;
                }
            });

        $this->info("{$pruned} lote(s) de importação expirado(s).");

        return self::SUCCESS;
    }
}

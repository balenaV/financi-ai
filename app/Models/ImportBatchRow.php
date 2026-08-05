<?php

namespace App\Models;

use App\Enums\ImportRowStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['posted_at', 'description_raw', 'description', 'type', 'amount', 'external_id', 'fingerprint', 'suggested_category_id', 'status', 'included', 'line_number'])]
class ImportBatchRow extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => ImportRowStatus::class,
            'amount' => 'decimal:2',
            'posted_at' => 'date',
            'included' => 'boolean',
        ];
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public function suggestedCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'suggested_category_id');
    }
}

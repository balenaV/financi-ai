<?php

namespace App\Models;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportFormat;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['account_id', 'filename', 'format', 'status', 'stored_path', 'rows_read', 'rows_imported', 'rows_duplicated', 'period_start', 'period_end', 'error'])]
class ImportBatch extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'format' => ImportFormat::class,
            'status' => ImportBatchStatus::class,
            'period_start' => 'date',
            'period_end' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportBatchRow::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

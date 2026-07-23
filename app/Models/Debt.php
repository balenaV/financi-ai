<?php

namespace App\Models;

use App\Enums\DebtStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'creditor', 'kind', 'description', 'original_amount', 'expected_total_amount', 'interest_rate', 'started_at', 'due_date', 'installment_count', 'status', 'notes'])]
class Debt extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => DebtStatus::class,
            'original_amount' => 'decimal:2',
            'expected_total_amount' => 'decimal:2',
            'interest_rate' => 'decimal:4',
            'started_at' => 'date',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(DebtInstallment::class)->orderBy('number');
    }
}

<?php

namespace App\Models;

use App\Enums\InvestmentOperationType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['investment_id', 'account_id', 'transaction_id', 'type', 'amount', 'quantity', 'operation_date', 'notes'])]
class InvestmentOperation extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => InvestmentOperationType::class,
            'amount' => 'decimal:2',
            'quantity' => 'decimal:8',
            'operation_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}

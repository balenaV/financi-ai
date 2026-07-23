<?php

namespace App\Models;

use App\Enums\InvestmentStatus;
use App\Enums\InvestmentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'type', 'institution', 'ticker', 'quantity', 'invested_amount', 'current_amount', 'last_updated_at', 'liquidity', 'maturity_date', 'status', 'notes'])]
class Investment extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => InvestmentType::class,
            'status' => InvestmentStatus::class,
            'quantity' => 'decimal:8',
            'invested_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'last_updated_at' => 'date',
            'maturity_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(InvestmentOperation::class)->latest('operation_date');
    }
}

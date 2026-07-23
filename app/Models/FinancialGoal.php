<?php

namespace App\Models;

use App\Enums\GoalStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['account_id', 'name', 'description', 'target_amount', 'current_amount', 'deadline', 'color', 'status', 'use_account_balance'])]
class FinancialGoal extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => GoalStatus::class,
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'deadline' => 'date',
            'use_account_balance' => 'boolean',
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
}

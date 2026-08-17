<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['financial_goal_id', 'amount', 'contributed_at'])]
class GoalContribution extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'contributed_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(FinancialGoal::class, 'financial_goal_id');
    }
}

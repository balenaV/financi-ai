<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'issuer', 'last_four', 'credit_limit', 'closing_day', 'due_day', 'color', 'active', 'notes'])]
class CreditCard extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'closing_day' => 'integer',
            'due_day' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(CreditCardBill::class)->orderByDesc('reference_month');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

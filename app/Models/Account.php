<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'type', 'institution', 'initial_balance', 'initial_balance_date', 'color', 'icon', 'currency', 'active', 'notes'])]
class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'initial_balance' => 'decimal:2',
            'initial_balance_date' => 'date',
            'active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'destination_account_id');
    }
}

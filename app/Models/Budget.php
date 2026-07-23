<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'month', 'year', 'limit_amount', 'notes'])]
class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['limit_amount' => 'decimal:2', 'month' => 'integer', 'year' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

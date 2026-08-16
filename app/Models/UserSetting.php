<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['currency', 'timezone', 'financial_month_start_day', 'view_preference', 'theme', 'hide_values', 'confirm_deletion', 'sections'])]
class UserSetting extends Model
{
    protected function casts(): array
    {
        return [
            'hide_values' => 'boolean',
            'confirm_deletion' => 'boolean',
            'financial_month_start_day' => 'integer',
            'sections' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

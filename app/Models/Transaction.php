<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['account_id', 'destination_account_id', 'credit_card_id', 'credit_card_bill_id', 'category_id', 'type', 'payment_channel', 'description', 'amount', 'competence_date', 'due_date', 'paid_at', 'status', 'notes', 'installment_group_id', 'installment_number', 'installment_total', 'recurrence_group_id', 'source_type', 'source_id'])]
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'competence_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'date',
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

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'destination_account_id');
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function creditCardBill(): BelongsTo
    {
        return $this->belongsTo(CreditCardBill::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

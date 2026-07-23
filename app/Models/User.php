<?php

namespace App\Models;

use App\Services\DefaultCategoryService;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->settings()->create();
            app(DefaultCategoryService::class)->seed($user);
        });
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function debtInstallments(): HasMany
    {
        return $this->hasMany(DebtInstallment::class);
    }

    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class);
    }

    public function creditCardBills(): HasMany
    {
        return $this->hasMany(CreditCardBill::class);
    }

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    public function investmentOperations(): HasMany
    {
        return $this->hasMany(InvestmentOperation::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function financialGoals(): HasMany
    {
        return $this->hasMany(FinancialGoal::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

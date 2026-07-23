<?php

namespace App\Services;

use App\Enums\CategoryType;
use App\Models\User;

class DefaultCategoryService
{
    private const INCOME = ['Salário', 'Renda extra', 'Freelance', 'Rendimentos', 'Reembolso', 'Presente', 'Outros'];

    private const EXPENSE = ['Alimentação', 'Moradia', 'Transporte', 'Saúde', 'Educação', 'Lazer', 'Assinaturas', 'Compras', 'Impostos', 'Dívidas', 'Outros'];

    public function seed(User $user): void
    {
        foreach (self::INCOME as $name) {
            $user->categories()->firstOrCreate(
                ['name' => $name, 'type' => CategoryType::Income->value],
                ['color' => '#1d9e75', 'icon' => 'income', 'active' => true],
            );
        }

        foreach (self::EXPENSE as $name) {
            $user->categories()->firstOrCreate(
                ['name' => $name, 'type' => CategoryType::Expense->value],
                ['color' => '#64748b', 'icon' => 'expense', 'active' => true],
            );
        }
    }
}

<?php

namespace App\Services;

use App\Enums\CategoryType;
use App\Models\User;

class DefaultCategoryService
{
    /**
     * Nome => [cor, ícone]. Cor/ícone vêm de App\Support\CategoryPalette —
     * mesma paleta validada em CategoryRequest. Onde o handoff (dashboard.html)
     * traz um exemplo canônico para o nome (Moradia, Alimentação, Transporte,
     * Compras, Assinaturas, Saúde, Lazer), usamos exatamente aquele par.
     */
    private const INCOME = [
        'Salário' => ['#137A4A', 'fa-solid fa-briefcase'],
        'Renda extra' => ['#2E9E5B', 'fa-solid fa-piggy-bank'],
        'Freelance' => ['#38C172', 'fa-solid fa-briefcase'],
        'Rendimentos' => ['#137A4A', 'fa-solid fa-piggy-bank'],
        'Reembolso' => ['#3C6E9F', 'fa-solid fa-cart-shopping'],
        'Presente' => ['#B03A6E', 'fa-solid fa-tag'],
        'Outros' => ['#5B5A54', 'fa-solid fa-tag'],
    ];

    private const EXPENSE = [
        'Alimentação' => ['#C0392B', 'fa-solid fa-utensils'],
        'Moradia' => ['#1F6F8B', 'fa-solid fa-house'],
        'Transporte' => ['#3C6E9F', 'fa-solid fa-car'],
        'Saúde' => ['#D68910', 'fa-solid fa-heart-pulse'],
        'Educação' => ['#38C172', 'fa-solid fa-graduation-cap'],
        'Lazer' => ['#2E9E5B', 'fa-solid fa-plane'],
        'Assinaturas' => ['#B03A6E', 'fa-solid fa-film'],
        'Compras' => ['#6B4FA8', 'fa-solid fa-cart-shopping'],
        'Impostos' => ['#5B5A54', 'fa-solid fa-briefcase'],
        'Dívidas' => ['#C0392B', 'fa-solid fa-bolt'],
        'Outros' => ['#5B5A54', 'fa-solid fa-tag'],
    ];

    public function seed(User $user): void
    {
        foreach (self::INCOME as $name => [$color, $icon]) {
            $user->categories()->firstOrCreate(
                ['name' => $name, 'type' => CategoryType::Income->value],
                ['color' => $color, 'icon' => $icon, 'active' => true],
            );
        }

        foreach (self::EXPENSE as $name => [$color, $icon]) {
            $user->categories()->firstOrCreate(
                ['name' => $name, 'type' => CategoryType::Expense->value],
                ['color' => $color, 'icon' => $icon, 'active' => true],
            );
        }
    }
}

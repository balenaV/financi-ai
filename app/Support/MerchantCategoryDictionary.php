<?php

namespace App\Support;

use App\Enums\CategoryType;
use App\Enums\TransactionType;

final class MerchantCategoryDictionary
{
    /**
     * Ordered — more specific keys must come before generic ones
     * (e.g. "uber eats" before "uber") since matching stops at the first hit.
     *
     * @var list<array{key:string,type:TransactionType,category:string}>
     */
    private const ENTRIES = [
        ['key' => 'ifood', 'type' => TransactionType::Expense, 'category' => 'Alimentação'],
        ['key' => 'rappi', 'type' => TransactionType::Expense, 'category' => 'Alimentação'],
        ['key' => 'uber eats', 'type' => TransactionType::Expense, 'category' => 'Alimentação'],
        ['key' => 'mcdonalds', 'type' => TransactionType::Expense, 'category' => 'Alimentação'],
        ['key' => 'burger king', 'type' => TransactionType::Expense, 'category' => 'Alimentação'],
        ['key' => 'supermercado', 'type' => TransactionType::Expense, 'category' => 'Alimentação'],
        ['key' => 'padaria', 'type' => TransactionType::Expense, 'category' => 'Alimentação'],
        ['key' => 'restaurante', 'type' => TransactionType::Expense, 'category' => 'Alimentação'],
        ['key' => 'uber', 'type' => TransactionType::Expense, 'category' => 'Transporte'],
        ['key' => '99app', 'type' => TransactionType::Expense, 'category' => 'Transporte'],
        ['key' => 'posto', 'type' => TransactionType::Expense, 'category' => 'Transporte'],
        ['key' => 'combustivel', 'type' => TransactionType::Expense, 'category' => 'Transporte'],
        ['key' => 'estacionamento', 'type' => TransactionType::Expense, 'category' => 'Transporte'],
        ['key' => 'pedagio', 'type' => TransactionType::Expense, 'category' => 'Transporte'],
        ['key' => 'netflix', 'type' => TransactionType::Expense, 'category' => 'Assinaturas'],
        ['key' => 'spotify', 'type' => TransactionType::Expense, 'category' => 'Assinaturas'],
        ['key' => 'amazon prime', 'type' => TransactionType::Expense, 'category' => 'Assinaturas'],
        ['key' => 'disney plus', 'type' => TransactionType::Expense, 'category' => 'Assinaturas'],
        ['key' => 'hbo max', 'type' => TransactionType::Expense, 'category' => 'Assinaturas'],
        ['key' => 'drogaria', 'type' => TransactionType::Expense, 'category' => 'Saúde'],
        ['key' => 'farmacia', 'type' => TransactionType::Expense, 'category' => 'Saúde'],
        ['key' => 'hospital', 'type' => TransactionType::Expense, 'category' => 'Saúde'],
        ['key' => 'clinica', 'type' => TransactionType::Expense, 'category' => 'Saúde'],
        ['key' => 'academia', 'type' => TransactionType::Expense, 'category' => 'Saúde'],
        ['key' => 'amazon', 'type' => TransactionType::Expense, 'category' => 'Compras'],
        ['key' => 'magazine luiza', 'type' => TransactionType::Expense, 'category' => 'Compras'],
        ['key' => 'mercado livre', 'type' => TransactionType::Expense, 'category' => 'Compras'],
        ['key' => 'shopee', 'type' => TransactionType::Expense, 'category' => 'Compras'],
        ['key' => 'aliexpress', 'type' => TransactionType::Expense, 'category' => 'Compras'],
        ['key' => 'aluguel', 'type' => TransactionType::Expense, 'category' => 'Moradia'],
        ['key' => 'condominio', 'type' => TransactionType::Expense, 'category' => 'Moradia'],
        ['key' => 'escola', 'type' => TransactionType::Expense, 'category' => 'Educação'],
        ['key' => 'faculdade', 'type' => TransactionType::Expense, 'category' => 'Educação'],
        ['key' => 'universidade', 'type' => TransactionType::Expense, 'category' => 'Educação'],
        ['key' => 'cinema', 'type' => TransactionType::Expense, 'category' => 'Lazer'],
        ['key' => 'salario', 'type' => TransactionType::Income, 'category' => 'Salário'],
        ['key' => 'freelance', 'type' => TransactionType::Income, 'category' => 'Freelance'],
        ['key' => 'reembolso', 'type' => TransactionType::Income, 'category' => 'Reembolso'],
    ];

    /** @return array{type:CategoryType,category:string}|null */
    public static function match(string $normalizedDescription, TransactionType $type): ?array
    {
        foreach (self::ENTRIES as $entry) {
            if ($entry['type'] !== $type) {
                continue;
            }

            if (str_contains($normalizedDescription, $entry['key'])) {
                return [
                    'type' => $type === TransactionType::Income ? CategoryType::Income : CategoryType::Expense,
                    'category' => $entry['category'],
                ];
            }
        }

        return null;
    }
}

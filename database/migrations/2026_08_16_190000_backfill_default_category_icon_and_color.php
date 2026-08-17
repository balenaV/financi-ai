<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Snapshot da paleta corrigida em App\Services\DefaultCategoryService no
     * momento desta migration. Propositalmente duplicado aqui (em vez de
     * chamar o service) para a migration continuar determinística mesmo que
     * a paleta padrão mude de novo no futuro.
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

    /**
     * Corrige categorias padrão já semeadas com o bug antigo do
     * DefaultCategoryService (icon literal "income"/"expense", cor fora da
     * paleta nova). Só toca linhas que ainda têm exatamente os valores
     * quebrados originais — categorias que o usuário já customizou (cor/ícone
     * diferentes desses) ficam intocadas.
     */
    public function up(): void
    {
        foreach (['income' => self::INCOME, 'expense' => self::EXPENSE] as $type => $map) {
            $brokenColor = $type === 'income' ? '#1d9e75' : '#64748b';

            foreach ($map as $name => [$color, $icon]) {
                DB::table('categories')
                    ->where('type', $type)
                    ->where('name', $name)
                    ->where('icon', $type)
                    ->where('color', $brokenColor)
                    ->update(['color' => $color, 'icon' => $icon]);
            }
        }
    }

    public function down(): void
    {
        // Irreversível com segurança: depois de aplicada, não há como distinguir
        // as linhas que esta migration corrigiu das que o usuário já tinha
        // customizado manualmente para os mesmos valores novos.
    }
};

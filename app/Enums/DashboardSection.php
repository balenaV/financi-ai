<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * As seções opcionais da barra do painel (fora "Visão geral", que é fixa).
 * O usuário escolhe exatamente 5 para ficarem na barra; as demais aparecem
 * em "Mais opções" — ver Configurações › Seções.
 */
enum DashboardSection: string
{
    use HasOptions;

    case Transacoes = 'transacoes';
    case Assinaturas = 'assinaturas';
    case Planejamento = 'planejamento';
    case Contas = 'contas';
    case Cartoes = 'cartoes';
    case Dividas = 'dividas';
    case Investimentos = 'investimentos';
    case Orcamentos = 'orcamentos';
    case Metas = 'metas';
    case Relatorios = 'relatorios';
    case Categorias = 'categorias';
    case Previsao = 'previsao';

    public function label(): string
    {
        return match ($this) {
            self::Transacoes => 'Transações',
            self::Assinaturas => 'Assinaturas',
            self::Planejamento => 'Planejamento',
            self::Contas => 'Contas',
            self::Cartoes => 'Cartões',
            self::Dividas => 'Dívidas',
            self::Investimentos => 'Investimentos',
            self::Orcamentos => 'Orçamentos',
            self::Metas => 'Metas',
            self::Relatorios => 'Relatórios',
            self::Categorias => 'Categorias',
            self::Previsao => 'Previsão',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Transacoes => 'fa-solid fa-arrow-right-arrow-left',
            self::Assinaturas => 'fa-solid fa-rotate',
            self::Planejamento => 'fa-regular fa-calendar-days',
            self::Contas => 'fa-solid fa-building-columns',
            self::Cartoes => 'fa-regular fa-credit-card',
            self::Dividas => 'fa-solid fa-file-invoice-dollar',
            self::Investimentos => 'fa-solid fa-seedling',
            self::Orcamentos => 'fa-solid fa-sliders',
            self::Metas => 'fa-solid fa-bullseye',
            self::Relatorios => 'fa-solid fa-chart-column',
            self::Categorias => 'fa-solid fa-tags',
            self::Previsao => 'fa-solid fa-chart-line',
        };
    }

    /** As 5 seções pré-selecionadas para quem ainda não escolheu as suas. */
    public static function defaults(): array
    {
        return [self::Transacoes->value, self::Assinaturas->value, self::Planejamento->value, self::Contas->value, self::Cartoes->value];
    }
}

<?php

namespace App\Support;

/**
 * Fonte única da paleta de cor/ícone de categorias do design system.
 * CategoryRequest valida contra estas listas; DefaultCategoryService também
 * deve construir a partir delas para não divergir se a paleta mudar.
 */
class CategoryPalette
{
    public const COLORS = [
        '#137A4A', '#2E9E5B', '#38C172', '#1F6F8B', '#3C6E9F',
        '#6B4FA8', '#B03A6E', '#C0392B', '#D68910', '#5B5A54',
    ];

    public const ICONS = [
        'fa-solid fa-tag', 'fa-solid fa-cart-shopping', 'fa-solid fa-house', 'fa-solid fa-car',
        'fa-solid fa-utensils', 'fa-solid fa-heart-pulse', 'fa-solid fa-graduation-cap', 'fa-solid fa-plane',
        'fa-solid fa-film', 'fa-solid fa-shirt', 'fa-solid fa-paw', 'fa-solid fa-briefcase',
        'fa-solid fa-piggy-bank', 'fa-solid fa-bolt', 'fa-solid fa-wifi', 'fa-solid fa-dumbbell',
    ];
}

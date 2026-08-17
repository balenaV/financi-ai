<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\CategoryPalette;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class DefaultCategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_default_category_uses_a_color_and_icon_from_the_shared_palette(): void
    {
        $user = User::factory()->create();

        $categories = $user->categories()->get();

        $this->assertGreaterThan(0, $categories->count());

        foreach ($categories as $category) {
            $this->assertContains(
                $category->color,
                CategoryPalette::COLORS,
                "Categoria '{$category->name}' ({$category->type->value}) tem cor '{$category->color}' fora da paleta do design system."
            );
            $this->assertContains(
                $category->icon,
                CategoryPalette::ICONS,
                "Categoria '{$category->name}' ({$category->type->value}) tem ícone '{$category->icon}' fora do set permitido."
            );
        }
    }

    public function test_seeded_default_categories_pass_the_manual_category_validation_rules(): void
    {
        $user = User::factory()->create();

        $rules = ['color' => ['required', Rule::in(CategoryPalette::COLORS)],
            'icon' => ['required', Rule::in(CategoryPalette::ICONS)]];

        foreach ($user->categories()->get() as $category) {
            $validator = Validator::make(
                ['color' => $category->color, 'icon' => $category->icon],
                $rules,
            );

            $this->assertTrue(
                $validator->passes(),
                "Categoria '{$category->name}' falhou na validação de cor/ícone: ".$validator->errors()
            );
        }
    }
}

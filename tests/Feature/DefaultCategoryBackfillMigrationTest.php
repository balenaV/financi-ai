<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultCategoryBackfillMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_fixes_categories_still_on_the_legacy_broken_values_without_touching_customized_ones(): void
    {
        $user = User::factory()->create();

        // Simula uma categoria semeada antes da correção do DefaultCategoryService.
        $user->categories()->where('name', 'Moradia')->where('type', 'expense')
            ->update(['icon' => 'expense', 'color' => '#64748b']);

        // Simula uma categoria que o usuário já personalizou manualmente.
        $user->categories()->where('name', 'Alimentação')->where('type', 'expense')
            ->update(['icon' => 'fa-solid fa-wifi', 'color' => '#6B4FA8']);

        $migration = require database_path('migrations/2026_08_16_190000_backfill_default_category_icon_and_color.php');
        $migration->up();

        $moradia = $user->categories()->where('name', 'Moradia')->where('type', 'expense')->first();
        $this->assertSame('#1F6F8B', $moradia->color);
        $this->assertSame('fa-solid fa-house', $moradia->icon);

        $alimentacao = $user->categories()->where('name', 'Alimentação')->where('type', 'expense')->first();
        $this->assertSame('#6B4FA8', $alimentacao->color);
        $this->assertSame('fa-solid fa-wifi', $alimentacao->icon);
    }
}

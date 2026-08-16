<?php

namespace Tests\Feature;

use App\Enums\DashboardSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_choose_exactly_five_sections_for_the_bar(): void
    {
        $user = User::factory()->create();
        $chosen = ['dividas', 'investimentos', 'metas', 'relatorios', 'categorias'];

        $this->actingAs($user)->patch(route('settings.sections'), ['sections' => $chosen])
            ->assertRedirect(route('dashboard').'#config');

        $this->assertSame($chosen, $user->settings()->first()->sections);
    }

    public function test_sections_selection_is_rejected_when_not_exactly_five(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('settings.sections'), [
            'sections' => ['dividas', 'investimentos'],
        ])->assertSessionHasErrors('sections');
    }

    public function test_sections_selection_is_rejected_with_an_unknown_section(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('settings.sections'), [
            'sections' => ['dividas', 'investimentos', 'metas', 'relatorios', 'inexistente'],
        ])->assertSessionHasErrors('sections.4');
    }

    public function test_sections_selection_is_rejected_with_duplicates(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('settings.sections'), [
            'sections' => ['dividas', 'dividas', 'metas', 'relatorios', 'categorias'],
        ])->assertSessionHasErrors('sections.0');
    }

    public function test_dashboard_falls_back_to_default_sections_without_a_saved_preference(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        foreach (DashboardSection::defaults() as $key) {
            $response->assertSee('data-goto="'.$key.'"', false);
        }
    }
}

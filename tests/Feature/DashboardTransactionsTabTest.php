<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTransactionsTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_the_transactions_tab(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $user->transactions()->create($this->payload($account, 'Aluguel de agosto', '2200.00'));
        $user->transactions()->create($this->payload($account, 'Mercado da esquina', '150.00'));

        $response = $this->actingAs($user)->get('/dashboard?tx_search=aluguel');

        $response->assertSee('Aluguel de agosto');
        $response->assertSee('Mostrando 1–1 de 1 transação');
    }

    public function test_pagination_shows_eight_transactions_per_page(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        for ($i = 0; $i < 10; $i++) {
            $user->transactions()->create($this->payload($account, "Compra {$i}", '10.00'));
        }

        $page1 = $this->actingAs($user)->get('/dashboard');
        $page1->assertSee('Mostrando 1–8 de 10 transações');

        $page2 = $this->actingAs($user)->get('/dashboard?tx_page=2');
        $page2->assertSee('Mostrando 9–10 de 10 transações');
    }

    public function test_transactions_tab_never_shows_another_users_data(): void
    {
        $victim = User::factory()->create();
        $victimAccount = Account::factory()->for($victim)->create();
        $victim->transactions()->create($this->payload($victimAccount, 'Segredo do outro usuário', '999.00'));

        $attacker = User::factory()->create();

        $response = $this->actingAs($attacker)->get('/dashboard');

        $response->assertDontSee('Segredo do outro usuário');
    }

    private function payload(Account $account, string $description, string $amount): array
    {
        return [
            'account_id' => $account->id,
            'category_id' => null,
            'type' => 'expense',
            'description' => $description,
            'amount' => $amount,
            'competence_date' => today(),
            'status' => 'completed',
        ];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_filters_and_calculations_render_only_the_users_data(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $account = Account::factory()->for($user)->create(['initial_balance' => '1000.00']);
        $foreign = Account::factory()->for($other)->create(['initial_balance' => '999999.00']);
        $this->createIncome($user, $account, '500.00');
        $this->createIncome($other, $foreign, '800000.00');

        $response = $this->actingAs($user)->get(route('dashboard', [
            'month' => now()->month,
            'year' => now()->year,
            'account_id' => $account->id,
        ]));

        $response->assertOk()->assertSee('Visão geral')->assertSee('1.500,00')->assertDontSee('999.999');
    }

    public function test_reports_and_csv_export_are_available(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $this->createIncome($user, $account, '250.00');

        $this->actingAs($user)->get(route('reports.index', [
            'type' => 'cash_flow',
            'start_date' => today()->startOfMonth()->toDateString(),
            'end_date' => today()->endOfMonth()->toDateString(),
        ]))->assertOk()->assertSee('Fluxo de caixa')->assertSee('Receita teste');

        $this->actingAs($user)->get(route('reports.export', [
            'start_date' => today()->startOfMonth()->toDateString(),
            'end_date' => today()->endOfMonth()->toDateString(),
        ]))->assertOk()->assertDownload();

        $this->actingAs($user)->get(route('transactions.export'))->assertOk()->assertDownload();
    }

    public function test_dashboard_rejects_foreign_account_filter(): void
    {
        $user = User::factory()->create();
        $foreign = Account::factory()->for(User::factory())->create();

        $this->actingAs($user)->get(route('dashboard', ['account_id' => $foreign->id]))->assertNotFound();
    }

    private function createIncome(User $user, Account $account, string $amount): void
    {
        $user->transactions()->create([
            'account_id' => $account->id,
            'category_id' => $user->categories()->where('name', 'Salário')->value('id'),
            'type' => 'income',
            'description' => 'Receita teste',
            'amount' => $amount,
            'competence_date' => today(),
            'paid_at' => today(),
            'status' => 'completed',
        ]);
    }
}

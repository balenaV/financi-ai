<?php

namespace Tests\Feature;

use App\Enums\DebtInstallmentStatus;
use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtInstallment;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_must_verify_email_before_accessing_financial_data(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect(route('verification.notice'));
    }

    public function test_csv_import_deduplicates_transactions(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $csv = "Data;Descrição;Valor\n25/07/2026;Mercado;-125,90\n25/07/2026;Salário;3000,00\n";

        $this->actingAs($user)->post(route('transactions.import.store'), [
            'account_id' => $account->id,
            'file' => UploadedFile::fake()->createWithContent('extrato.csv', $csv),
        ])->assertRedirect(route('transactions.index'));

        $this->assertDatabaseCount('transactions', 2);
        $this->assertDatabaseHas('transactions', ['description' => 'Mercado', 'amount' => 125.90, 'type' => 'expense']);

        $this->actingAs($user)->post(route('transactions.import.store'), [
            'account_id' => $account->id,
            'file' => UploadedFile::fake()->createWithContent('extrato.csv', $csv),
        ])->assertSessionHas('success', fn ($message) => str_contains($message, '2 duplicadas'));

        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_reminder_command_creates_only_one_notification_per_obligation(): void
    {
        $user = User::factory()->create();
        $debt = Debt::factory()->for($user)->create();
        DebtInstallment::factory()->for($user)->for($debt)->create([
            'due_date' => today()->addDays(2),
            'status' => DebtInstallmentStatus::Pending,
        ]);

        $this->artisan('finance:send-reminders')->assertSuccessful();
        $this->artisan('finance:send-reminders')->assertSuccessful();

        $this->assertCount(1, $user->fresh()->notifications);
    }

    public function test_mutating_request_is_written_to_security_history(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('settings.toggle-theme'))->assertSuccessful();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'updated',
            'route' => 'settings.toggle-theme',
        ]);
    }

    public function test_pwa_assets_are_public(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('financi.ai', $manifest['short_name']);
        $this->assertStringContainsString('financi-ai-shell-v1', file_get_contents(public_path('service-worker.js')));
    }

    public function test_future_income_appears_in_forecast_and_projected_dashboard_totals(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $date = today()->addMonths(2);

        $user->transactions()->create([
            'account_id' => $account->id,
            'type' => 'income',
            'payment_channel' => 'account',
            'description' => 'Bônus futuro',
            'amount' => '2500.00',
            'competence_date' => $date,
            'due_date' => $date,
            'status' => 'planned',
        ]);

        $dashboard = app(DashboardService::class)->build($user, [
            'month' => $date->month,
            'year' => $date->year,
        ]);

        $this->assertSame('2500.00', $dashboard['summary']['planned_income']);
        $this->assertSame('2500.00', $dashboard['summary']['forecast_income']);
        $this->assertSame('2500.00', $dashboard['summary']['forecast_result']);

        $this->actingAs($user)->get(route('forecast.index'))
            ->assertSuccessful()
            ->assertSee('Bônus futuro')
            ->assertSee('Adicionar ganho futuro');
    }

    public function test_mail_diagnostics_identifies_local_log_driver(): void
    {
        config(['mail.default' => 'log']);

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('não envia mensagens')
            ->assertSuccessful();
    }
}

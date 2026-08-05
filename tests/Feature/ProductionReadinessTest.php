<?php

namespace Tests\Feature;

use App\Enums\DebtInstallmentStatus;
use App\Enums\ImportBatchStatus;
use App\Enums\ImportFormat;
use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtInstallment;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_forwarded_https_is_used_for_secure_form_actions(): void
    {
        $this->withHeaders([
            'Host' => 'financiai.cloud',
            'X-Forwarded-Host' => 'financiai.cloud',
            'X-Forwarded-Proto' => 'https',
        ])->get('http://financiai.cloud/login')
            ->assertSuccessful()
            ->assertSee('action="https://financiai.cloud/login"', false);
    }

    public function test_import_wizard_page_renders(): void
    {
        $user = User::factory()->create();
        Account::factory()->for($user)->create();

        $this->actingAs($user)->get(route('transactions.import.create'))
            ->assertOk()
            ->assertSee('Importar extrato')
            ->assertSee('Arraste seu extrato aqui');
    }

    public function test_csv_import_deduplicates_transactions(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $csv = "Data;Descrição;Valor\n25/07/2026;Mercado;-125,90\n25/07/2026;Salário;3000,00\n";

        $firstBatchId = $this->importCsv($user, $account, $csv);
        $this->assertDatabaseCount('transactions', 2);
        $this->assertDatabaseHas('transactions', ['description' => 'Mercado', 'amount' => 125.90, 'type' => 'expense']);
        $this->assertDatabaseHas('import_batches', ['id' => $firstBatchId, 'status' => 'committed', 'rows_imported' => 2]);

        // Importar o mesmo arquivo de novo: as duas linhas devem chegar como
        // "duplicate_probable" (mesmo fingerprint), desmarcadas por padrão.
        $secondBatchId = $this->createAndPreviewCsv($user, $account, $csv);
        $rows = $this->getJson(route('transactions.import.show', $secondBatchId))->json('rows');
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertSame('duplicate_probable', $row['status']);
            $this->assertFalse($row['included']);
        }

        // O usuário não força nada incluído (comportamento padrão do wizard): nenhuma transação nova.
        $this->postJson(route('transactions.import.commit', $secondBatchId), ['row_ids' => []])
            ->assertStatus(422);

        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_reverting_an_import_batch_removes_its_transactions(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $csv = "Data;Descrição;Valor\n25/07/2026;Mercado;-125,90\n";

        $batchId = $this->importCsv($user, $account, $csv);
        $this->assertDatabaseCount('transactions', 1);

        $this->actingAs($user)->post(route('transactions.import.revert', $batchId))
            ->assertRedirect(route('transactions.index'));

        // Transaction uses SoftDeletes like every other financial record — revert
        // preserves the audit trail rather than hard-deleting the row.
        $this->assertSoftDeleted('transactions', ['import_batch_id' => $batchId]);
        $this->assertDatabaseHas('import_batches', ['id' => $batchId, 'status' => 'reverted']);
    }

    public function test_abandoned_import_upload_is_pruned_and_file_deleted(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        Storage::disk('local')->put('imports/abandoned.csv', 'Data;Descrição;Valor');
        $batch = $user->importBatches()->create([
            'account_id' => $account->id,
            'filename' => 'abandoned.csv',
            'format' => ImportFormat::Csv,
            'status' => ImportBatchStatus::Pending,
            'stored_path' => 'imports/abandoned.csv',
        ]);
        $batch->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->artisan('imports:prune-stale')->assertSuccessful();

        Storage::disk('local')->assertMissing('imports/abandoned.csv');
        $this->assertDatabaseHas('import_batches', ['id' => $batch->id, 'status' => 'failed', 'stored_path' => null]);
    }

    public function test_ofx_import_handles_iso_8859_1_accents(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $header = "OFXHEADER:100\r\nDATA:OFXSGML\r\nVERSION:102\r\nENCODING:USASCII\r\nCHARSET:1252\r\n\r\n";
        $body = '<OFX><BANKMSGSRSV1><STMTTRNRS><STMTRS><BANKTRANLIST>'
            .'<STMTTRN><TRNTYPE>DEBIT<DTPOSTED>20260725<TRNAMT>-89.90<FITID>FIT1<MEMO>PADARIA SÃO JOSÉ</STMTTRN>'
            .'</BANKTRANLIST></STMTRS></STMTTRNRS></BANKMSGSRSV1></OFX>';
        $ofx = $header.$this->toWindows1252($body);

        $storeResponse = $this->actingAs($user)->postJson(route('transactions.import.store'), [
            'account_id' => $account->id,
            'file' => UploadedFile::fake()->createWithContent('extrato.ofx', $ofx),
        ])->assertSuccessful();
        $batchId = $storeResponse->json('batch_id');

        $this->postJson(route('transactions.import.preview', $batchId), [])->assertSuccessful();

        $rows = $this->getJson(route('transactions.import.show', $batchId))->json('rows');
        $this->assertCount(1, $rows);
        $this->assertStringContainsString('PADARIA SÃO JOSÉ', $rows[0]['description']);
    }

    public function test_merchant_dictionary_suggests_a_category(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $csv = "Data;Descrição;Valor\n25/07/2026;IFOOD *RESTAURANTE SP;-68,90\n";

        $batchId = $this->createAndPreviewCsv($user, $account, $csv);
        $rows = $this->getJson(route('transactions.import.show', $batchId))->json('rows');

        $alimentacao = $user->categories()->where('name', 'Alimentação')->where('type', 'expense')->first();
        $this->assertSame($alimentacao->id, $rows[0]['suggested_category_id']);
        $this->assertSame('new', $rows[0]['status']);
    }

    public function test_correcting_a_category_creates_a_rule_used_on_the_next_import(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();
        $compras = $user->categories()->where('name', 'Compras')->where('type', 'expense')->first();

        // "Loja Xyz" não bate com nada do dicionário — não chuta, fica sem categoria.
        $firstCsv = "Data;Descrição;Valor\n25/07/2026;LOJA XYZ SUPRIMENTOS;-45,00\n";
        $firstBatch = $this->createAndPreviewCsv($user, $account, $firstCsv);
        $firstRows = $this->getJson(route('transactions.import.show', $firstBatch))->json('rows');
        $this->assertNull($firstRows[0]['suggested_category_id']);
        $this->assertSame('needs_category', $firstRows[0]['status']);

        $this->postJson(route('transactions.import.commit', $firstBatch), [
            'row_ids' => [$firstRows[0]['id']],
            'categories' => [$firstRows[0]['id'] => $compras->id],
        ])->assertSuccessful();

        $this->assertDatabaseHas('category_rules', [
            'user_id' => $user->id,
            'pattern' => 'loja',
            'category_id' => $compras->id,
            'hit_count' => 1,
        ]);

        // Segunda importação, merchant diferente mas mesma primeira palavra ("loja"):
        // a regra aprendida já sugere a categoria sozinha.
        $secondCsv = "Data;Descrição;Valor\n01/08/2026;LOJA XYZ SUPRIMENTOS;-30,00\n";
        $secondBatch = $this->createAndPreviewCsv($user, $account, $secondCsv);
        $secondRows = $this->getJson(route('transactions.import.show', $secondBatch))->json('rows');
        $this->assertSame($compras->id, $secondRows[0]['suggested_category_id']);
    }

    private function toWindows1252(string $utf8): string
    {
        return iconv('UTF-8', 'Windows-1252', $utf8);
    }

    private function importCsv(User $user, Account $account, string $csv): int
    {
        $batchId = $this->createAndPreviewCsv($user, $account, $csv);
        $rows = $this->getJson(route('transactions.import.show', $batchId))->json('rows');
        $rowIds = collect($rows)->where('included', true)->pluck('id')->all();

        $this->postJson(route('transactions.import.commit', $batchId), ['row_ids' => $rowIds])->assertSuccessful();

        return $batchId;
    }

    private function createAndPreviewCsv(User $user, Account $account, string $csv): int
    {
        $store = $this->actingAs($user)->postJson(route('transactions.import.store'), [
            'account_id' => $account->id,
            'file' => UploadedFile::fake()->createWithContent('extrato.csv', $csv),
        ])->assertSuccessful();

        $batchId = $store->json('batch_id');
        $mapping = $store->json('suggested_mapping');

        $this->postJson(route('transactions.import.preview', $batchId), [
            'column_map' => $mapping,
            'date_format' => 'DD/MM/AAAA',
            'decimal_separator' => 'virgula',
        ])->assertSuccessful();

        return $batchId;
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
        $this->assertContains('/images/brand/financi-ai-symbol.svg', array_column($manifest['icons'], 'src'));
        $this->assertStringContainsString('financi-ai-shell-v2', file_get_contents(public_path('service-worker.js')));
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

    public function test_mail_diagnostics_rejects_unsupported_smtp_scheme(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'scheme' => 'tls',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'mailer@example.com',
                'password' => 'app-password',
            ],
        ]);

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('Esquema SMTP inválido: tls')
            ->assertFailed();
    }

    public function test_mail_diagnostics_accepts_smtp_with_starttls_port(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'scheme' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'mailer@example.com',
                'password' => 'app-password',
            ],
        ]);

        $this->artisan('mail:diagnose')
            ->expectsOutputToContain('Esquema SMTP: smtp')
            ->assertSuccessful();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OfxImportCategorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_account_envelope_ofx_transaction_gets_a_suggested_category(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $ofx = $this->bankAccountEnvelope(
            '<STMTTRN><TRNTYPE>DEBIT<DTPOSTED>20260725<TRNAMT>-68.90<FITID>FIT1<MEMO>IFOOD *RESTAURANTE SP</STMTTRN>'
        );

        $rows = $this->importAndFetchRows($user, $account, $ofx);

        $alimentacao = $user->categories()->where('name', 'Alimentação')->where('type', 'expense')->first();
        $this->assertCount(1, $rows);
        $this->assertSame($alimentacao->id, $rows[0]['suggested_category_id']);
    }

    public function test_credit_card_envelope_ofx_transaction_gets_a_suggested_category(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $ofx = $this->creditCardEnvelope(
            '<STMTTRN><TRNTYPE>DEBIT<DTPOSTED>20260725<TRNAMT>-68.90<FITID>FIT1<MEMO>IFOOD *RESTAURANTE SP</STMTTRN>'
        );

        $rows = $this->importAndFetchRows($user, $account, $ofx);

        $alimentacao = $user->categories()->where('name', 'Alimentação')->where('type', 'expense')->first();
        $this->assertCount(1, $rows);
        $this->assertSame($alimentacao->id, $rows[0]['suggested_category_id']);
    }

    public function test_unsigned_debit_amount_is_treated_as_expense_and_still_categorized(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        // TRNAMT positivo (sem sinal), só o TRNTYPE indica que é uma despesa —
        // variação real de exportação que não assina o valor.
        $ofx = $this->bankAccountEnvelope(
            '<STMTTRN><TRNTYPE>DEBIT<DTPOSTED>20260725<TRNAMT>68.90<FITID>FIT1<MEMO>IFOOD *RESTAURANTE SP</STMTTRN>'
        );

        $rows = $this->importAndFetchRows($user, $account, $ofx);

        $alimentacao = $user->categories()->where('name', 'Alimentação')->where('type', 'expense')->first();
        $this->assertCount(1, $rows);
        $this->assertSame('expense', $rows[0]['type']);
        $this->assertSame($alimentacao->id, $rows[0]['suggested_category_id']);
    }

    public function test_unsigned_pos_amount_is_also_treated_as_expense(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        // TRNTYPE além de DEBIT também deve forçar o sinal — cobertura do
        // conjunto conservador de tipos de débito (POS, ATM, FEE, SRVCHG,
        // PAYMENT, DIRECTDEBIT, CHECK), não só o mais comum.
        $ofx = $this->bankAccountEnvelope(
            '<STMTTRN><TRNTYPE>POS<DTPOSTED>20260725<TRNAMT>68.90<FITID>FIT1<MEMO>IFOOD *RESTAURANTE SP</STMTTRN>'
        );

        $rows = $this->importAndFetchRows($user, $account, $ofx);

        $alimentacao = $user->categories()->where('name', 'Alimentação')->where('type', 'expense')->first();
        $this->assertCount(1, $rows);
        $this->assertSame('expense', $rows[0]['type']);
        $this->assertSame($alimentacao->id, $rows[0]['suggested_category_id']);
    }

    private function header(): string
    {
        return "OFXHEADER:100\r\nDATA:OFXSGML\r\nVERSION:102\r\nENCODING:USASCII\r\nCHARSET:NONE\r\n\r\n";
    }

    private function bankAccountEnvelope(string $transaction): string
    {
        return $this->header()
            .'<OFX><BANKMSGSRSV1><STMTTRNRS><STMTRS><CURDEF>BRL'
            .'<BANKACCTFROM><BANKID>001<ACCTID>12345<ACCTTYPE>CHECKING</BANKACCTFROM>'
            .'<BANKTRANLIST>'.$transaction.'</BANKTRANLIST>'
            .'</STMTRS></STMTTRNRS></BANKMSGSRSV1></OFX>';
    }

    private function creditCardEnvelope(string $transaction): string
    {
        return $this->header()
            .'<OFX><CREDITCARDMSGSRSV1><CCSTMTTRNRS><CCSTMTRS><CURDEF>BRL'
            .'<CCACCTFROM><ACCTID>1234567890123456</CCACCTFROM>'
            .'<BANKTRANLIST>'.$transaction.'</BANKTRANLIST>'
            .'</CCSTMTRS></CCSTMTTRNRS></CREDITCARDMSGSRSV1></OFX>';
    }

    private function importAndFetchRows(User $user, Account $account, string $ofx): array
    {
        $store = $this->actingAs($user)->postJson(route('transactions.import.store'), [
            'account_id' => $account->id,
            'file' => UploadedFile::fake()->createWithContent('extrato.ofx', $ofx),
        ])->assertSuccessful();

        $batchId = $store->json('batch_id');

        $this->postJson(route('transactions.import.preview', $batchId), [])->assertSuccessful();

        return $this->getJson(route('transactions.import.show', $batchId))->json('rows');
    }
}

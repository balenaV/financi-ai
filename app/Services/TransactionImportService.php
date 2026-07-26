<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TransactionImportService
{
    /** @return array{created:int,duplicates:int,invalid:int} */
    public function import(User $user, Account $account, UploadedFile $file, ?Category $category = null): array
    {
        $rows = match (strtolower($file->getClientOriginalExtension())) {
            'csv' => $this->fromCsv($file->getRealPath()),
            'ofx' => $this->fromOfx($file->getRealPath()),
            default => throw new RuntimeException('Formato de arquivo não suportado.'),
        };

        $result = ['created' => 0, 'duplicates' => 0, 'invalid' => 0];

        DB::transaction(function () use ($rows, $user, $account, $category, &$result) {
            foreach ($rows as $row) {
                try {
                    $amount = Money::normalize((string) ($row['amount'] ?? '0'));
                    $date = Carbon::parse($row['date'] ?? null)->toDateString();
                    $description = trim((string) ($row['description'] ?? ''));
                } catch (\Throwable) {
                    $result['invalid']++;

                    continue;
                }

                if ($description === '' || bccomp($amount, '0', 2) === 0) {
                    $result['invalid']++;

                    continue;
                }

                $type = str_starts_with($amount, '-') ? TransactionType::Expense : TransactionType::Income;
                $absolute = ltrim($amount, '-');
                $externalId = trim((string) ($row['external_id'] ?? ''));
                $hash = hash('sha256', implode('|', [
                    $account->id,
                    $externalId ?: $date,
                    Str::lower($description),
                    $absolute,
                ]));

                if ($user->transactions()->where('import_hash', $hash)->exists()) {
                    $result['duplicates']++;

                    continue;
                }

                $user->transactions()->create([
                    'account_id' => $account->id,
                    'category_id' => $category?->id,
                    'type' => $type,
                    'payment_channel' => 'account',
                    'description' => Str::limit($description, 180, ''),
                    'amount' => $absolute,
                    'competence_date' => $date,
                    'due_date' => $date,
                    'paid_at' => $date,
                    'status' => TransactionStatus::Completed,
                    'notes' => 'Importada de '.$account->name,
                    'source_type' => 'file_import',
                    'import_hash' => $hash,
                ]);
                $result['created']++;
            }
        });

        return $result;
    }

    /** @return list<array{date:string,description:string,amount:string,external_id?:string}> */
    private function fromCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Não foi possível ler o arquivo.');
        }

        $first = fgets($handle);
        rewind($handle);
        $delimiter = substr_count((string) $first, ';') >= substr_count((string) $first, ',') ? ';' : ',';
        $headers = array_map(fn ($header) => $this->header((string) $header), fgetcsv($handle, 0, $delimiter) ?: []);
        $rows = [];

        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = array_combine($headers, array_pad($values, count($headers), ''));
            if (! is_array($row)) {
                continue;
            }

            $date = $row['date'] ?? null;
            if ($date && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                $date = Carbon::createFromFormat('d/m/Y', $date)->toDateString();
            }

            $rows[] = [
                'date' => (string) $date,
                'description' => (string) ($row['description'] ?? ''),
                'amount' => (string) ($row['amount'] ?? ''),
                'external_id' => (string) ($row['external_id'] ?? ''),
            ];
        }

        fclose($handle);

        return $rows;
    }

    /** @return list<array{date:string,description:string,amount:string,external_id:string}> */
    private function fromOfx(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Não foi possível ler o arquivo.');
        }

        preg_match_all('/<STMTTRN>(.*?)(?:<\/STMTTRN>|(?=<STMTTRN>|<\/BANKTRANLIST>))/is', $contents, $matches);

        return array_map(function (string $block) {
            $field = fn (string $name): string => preg_match('/<'.$name.'>([^<\r\n]+)/i', $block, $match)
                ? trim($match[1])
                : '';
            $rawDate = preg_replace('/\D/', '', $field('DTPOSTED'));

            return [
                'date' => strlen((string) $rawDate) >= 8 ? substr((string) $rawDate, 0, 8) : '',
                'description' => $field('MEMO') ?: $field('NAME'),
                'amount' => $field('TRNAMT'),
                'external_id' => $field('FITID'),
            ];
        }, $matches[1]);
    }

    private function header(string $header): string
    {
        $normalized = Str::of($header)->trim()->lower()->ascii()->replace([' ', '-'], '_')->toString();

        return match ($normalized) {
            'data', 'data_lancamento', 'data_da_transacao' => 'date',
            'descricao', 'historico', 'memo', 'nome' => 'description',
            'valor', 'quantia' => 'amount',
            'id', 'fitid', 'identificador' => 'external_id',
            default => $normalized,
        };
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PixTransaction;

class CheckPixStatus extends Command
{
    protected $signature = 'pix:check-status';
    protected $description = 'Verifica mudanças de status nas transações Pix.';

    public function handle(): void
    {
        $this->info('⚠️ Comando desabilitado temporariamente.');
        return;

        // Código original mantido abaixo
        $transactions = PixTransaction::whereNotIn('status', ['paid', 'refunded', 'chargeback', 'in_protest'])->get();

        foreach ($transactions as $transaction) {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => 'Basic c2tfVV9paGVSNW53aUNVZjBGNFRMMXptUzluN011ZUtOT3V4WVgzWFc3aHFRNGNkNV91OnBrXy13dzU3T0RnOURyYTZFN3BzNHZuVzc2UFZtS1FQOXdCNkVYSFN0bXBhZldNUmtUaA==',
            ])->get("https://api.payonhub.com/v1/transactions/{$transaction->external_transaction_id}");

            if ($response->failed()) {
                Log::warning('Erro na API PayOnHub', ['id' => $transaction->external_transaction_id]);
                continue;
            }

            $newStatus = $response['status'] ?? null;

            if (! $newStatus || $newStatus === $transaction->status) {
                continue;
            }

            $oldStatus = $transaction->status;
            $transaction->update(['status' => $newStatus]);

            Log::info('Status da transação atualizado via comando.', [
                'external_id' => $transaction->external_transaction_id,
                'de' => $oldStatus,
                'para' => $newStatus,
            ]);

            $user = $transaction->user;

            if (! $user) {
                Log::warning('Usuário não encontrado para transação.', [
                    'transaction_id' => $transaction->id,
                ]);
                continue;
            }

            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $valorCentavos = $transaction->amount;
                $taxa = $user->taxa_cash_in ?? 0;

                $valorLiquidoCentavos = intval(round($valorCentavos * (1 - ($taxa / 100))));

                $user->increment('saldo', $valorLiquidoCentavos);

                Log::info('💰 PIX creditado com taxa', [
                    'user_id' => $user->id,
                    'valor_bruto_centavos' => $valorCentavos,
                    'taxa_percentual' => $taxa,
                    'valor_liquido_centavos' => $valorLiquidoCentavos,
                    'novo_saldo' => $user->fresh()->saldo,
                ]);
            }

            if (
                in_array($newStatus, ['refunded', 'chargeback', 'in_protest']) &&
                ! in_array($oldStatus, ['refunded', 'chargeback', 'in_protest'])
            ) {
                $user->decrement('saldo', $transaction->amount);
                $user->increment('bloqueado', $transaction->amount);
            }

            $this->info("Transação {$transaction->id} atualizada para {$newStatus}");
        }
    }
}

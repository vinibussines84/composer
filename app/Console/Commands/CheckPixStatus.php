<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\PixTransaction;
use Illuminate\Support\Facades\Log;

class CheckPixStatus extends Command
{
    protected $signature = 'pix:check-status';
    protected $description = 'Verifica mudanças de status nas transações Pix.';

    public function handle(): void
    {
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

            $status = $response['status'] ?? null;

            if ($status && $status !== $transaction->status) {
                $request = new \Illuminate\Http\Request([
                    'id' => $transaction->external_transaction_id,
                    'status' => $status,
                ]);

                // Chama o controller diretamente
                app(\App\Http\Controllers\Api\PixWebhookController::class)->handle($request);

                $this->info("Transação {$transaction->id} atualizada para {$status}");
            }
        }
    }
}

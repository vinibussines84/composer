<?php

namespace App\Services;

use App\Models\PixTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BloobankWebhookProcessor
{
    public function process(array $data): void
    {
        if (! isset($data['id'], $data['status'])) {
            throw new \Exception('Payload inválido: campos obrigatórios ausentes.');
        }

        $status = $data['status'];
        $bloobankId = $data['id'];

        $transaction = PixTransaction::where('external_transaction_id', $bloobankId)->first();

        if (! $transaction) {
            Log::warning("Webhook Bloobank: Transação não encontrada: $bloobankId");
            throw new \Exception('Transação não encontrada.');
        }

        $user = User::find($transaction->user_id);

        if (! $user) {
            Log::warning("Webhook Bloobank: Usuário não encontrado para transação $bloobankId");
            throw new \Exception('Usuário não encontrado.');
        }

        DB::beginTransaction();

        try {
            $valor = $transaction->amount; // em centavos
            $taxa = max(0, min(100, (float) ($user->taxa_cash_in ?? 0)));

            if ($status === 'approved' && $transaction->status !== 'paid') {
                $desconto = (int) round($valor * ($taxa / 100));
                $valorLiquido = $valor - $desconto;

                $user->increment('saldo', $valorLiquido);

                $transaction->update([
                    'status' => 'paid',
                ]);

                Log::info("✅ Pagamento aprovado manualmente: $bloobankId", [
                    'user_id' => $user->id,
                    'valor_bruto' => $valor,
                    'taxa' => $taxa,
                    'desconto' => $desconto,
                    'creditado' => $valorLiquido,
                ]);
            }

            if ($status === 'chargeback' && $transaction->status !== 'chargeback') {
                $user->decrement('saldo', $valor);

                $transaction->update([
                    'status' => 'chargeback',
                ]);

                Log::warning("⚠️ Chargeback manual: $bloobankId", [
                    'user_id' => $user->id,
                    'valor_retirado' => $valor,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("❌ Erro ao processar webhook manual Bloobank: " . $e->getMessage());
            throw $e;
        }
    }
}

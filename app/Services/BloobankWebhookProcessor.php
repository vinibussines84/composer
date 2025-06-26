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
        $body = $data['body'] ?? null;

        if (! $body || ! isset($body['id'], $body['status'])) {
            throw new \Exception('Payload inválido: campos obrigatórios ausentes.');
        }

        $status = $body['status'];
        $bloobankId = $body['id'];

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
                $descontoPercentual = (int) round($valor * ($taxa / 100));
                $descontoFixo = 1000; // R$10 em centavos

                $valorLiquido = $valor - $descontoPercentual - $descontoFixo;

                if ($valorLiquido < 0) {
                    $valorLiquido = 0;
                }

                // Credita valor líquido ao usuário original
                $user->increment('saldo', $valorLiquido);

                // Repasse fixo para conta central com transação registrada
                $central = User::where('is_central', true)->first();
                if ($central) {
                    $central->increment('saldo', $descontoFixo);

                    PixTransaction::create([
                        'user_id' => $central->id,
                        'authkey' => $central->authkey,
                        'gtkey' => $central->gtkey,
                        'amount' => $descontoFixo,
                        'status' => 'paid',
                        'type' => 'comissao',
                        'external_transaction_id' => 'repasse_' . $transaction->id,
                        'description' => "Comissão usuário ({$user->name}) - R$10,00",
                    ]);

                    Log::info("✅ R$10 repassados para conta central", [
                        'central_id' => $central->id,
                        'email' => $central->email,
                    ]);
                } else {
                    Log::warning("⚠️ Conta central não encontrada para repasse fixo");
                }

                $transaction->update([
                    'status' => 'paid',
                    'end_to_end_id' => $body['pix']['endToEndId'] ?? null,
                ]);

                Log::info("✅ Pagamento aprovado e creditado com taxas: $bloobankId", [
                    'user_id' => $user->id,
                    'valor_bruto' => $valor,
                    'taxa_percentual' => $taxa,
                    'desconto_percentual' => $descontoPercentual,
                    'desconto_fixo' => $descontoFixo,
                    'valor_liquido' => $valorLiquido,
                    'creditado_ao_user' => $valorLiquido,
                    'creditado_ao_central' => $descontoFixo,
                    'e2e' => $body['pix']['endToEndId'] ?? null,
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

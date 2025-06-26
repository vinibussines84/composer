<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PixTransaction;
use Illuminate\Support\Facades\Log;

class PixWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $externalId = $payload['id'] ?? null;
        $newStatus  = $payload['status'] ?? null;

        if (! $externalId || ! $newStatus) {
            Log::warning('Webhook recebido com payload inválido.', ['payload' => $payload]);
            return response()->json(['error' => 'Payload inválido'], 400);
        }

        $transaction = PixTransaction::where('external_transaction_id', $externalId)->first();

        if (! $transaction) {
            Log::warning('Webhook: ID inexistente.', ['external_id' => $externalId]);
            return response()->json(['error' => 'id inexistente'], 404);
        }

        $oldStatus = $transaction->status;

        if ($oldStatus === 'paid' && $newStatus === 'paid') {
            Log::info('Webhook ignorado: transação já está como paga.', [
                'external_id' => $externalId,
                'status' => $newStatus,
            ]);
            return response()->json(['success' => true]);
        }

        if ($oldStatus !== $newStatus) {
            $transaction->update([
                'status' => $newStatus,
            ]);

            Log::info('Webhook: Status da transação atualizado.', [
                'external_id' => $externalId,
                'de' => $oldStatus,
                'para' => $newStatus,
            ]);
        }

        $user = $transaction->user;

        if ($user) {
            $valor = $transaction->amount / 100; // em reais
            $taxa  = $user->taxa_cash_in ?? 0;

            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                // Desconta taxa percentual + R$10 fixo
                $taxaPercentual = $valor * ($taxa / 100);
                $descontoFixo = 10;
                $valorLiquido = $valor - $taxaPercentual - $descontoFixo;
                $valorCentavos = intval(round($valorLiquido * 100));

                if ($valorCentavos < 0) {
                    $valorCentavos = 0;
                }

                // Credita valor líquido ao usuário
                $user->increment('saldo', $valorCentavos);

                // Repasse fixo de R$10 para conta central
                $central = \App\Models\User::where('is_central', true)->first();
                if ($central) {
                    $central->increment('saldo', 1000);

                    Log::info('✅ R$10 repassados para conta central', [
                        'central_id' => $central->id,
                        'email' => $central->email,
                        'novo_saldo' => $central->saldo,
                    ]);
                } else {
                    Log::warning('⚠️ Conta central não encontrada para repasse');
                }

                Log::info('💰 PIX creditado com taxa e desconto fixo de R$10', [
                    'valor_bruto'            => $valor,
                    'taxa_percentual'        => $taxaPercentual,
                    'valor_liquido'          => $valorLiquido,
                    'adicionado_em_centavos' => $valorCentavos,
                    'user_id'                => $user->id,
                ]);
            }

            if (
                in_array($newStatus, ['refunded', 'chargeback', 'in_protest']) &&
                ! in_array($oldStatus, ['refunded', 'chargeback', 'in_protest'])
            ) {
                $user->decrement('saldo', $transaction->amount);
                $user->increment('bloqueado', $transaction->amount);
            }
        }

        return response()->json(['success' => true]);
    }
}

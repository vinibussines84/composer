<?php

namespace App\Services;

use App\Models\PixTransaction;
use Illuminate\Support\Facades\Log;

class PluggouWebhookProcessor
{
    public function process(array $payload): void
    {
        $type = $payload['type'] ?? null;

        if ($type !== 'pix.in.confirmation') {
            Log::warning('[PluggouWebhook] Tipo ignorado: ' . ($type ?? 'N/A'));
            return;
        }

        $data = $payload['data'] ?? [];

        $referenceCode = $data['referenceCode'] ?? null;
        $statusPluggou = strtoupper($data['status'] ?? '');

        if (!$referenceCode || !$statusPluggou) {
            Log::warning('[PluggouWebhook] Dados incompletos no payload', [
                'referenceCode' => $referenceCode,
                'status'        => $statusPluggou,
            ]);
            return;
        }

        $transaction = PixTransaction::where('reference_code', $referenceCode)->first();

        if (!$transaction) {
            Log::warning('[PluggouWebhook] Transação não encontrada', [
                'referenceCode' => $referenceCode,
            ]);
            return;
        }

        if ($transaction->status === 'paid') {
            Log::info('[PluggouWebhook] Transação já marcada como paga', [
                'referenceCode' => $referenceCode,
            ]);
            return;
        }

        if ($statusPluggou === 'APPROVED') {
            $transaction->update(['status' => 'paid']);

            $transaction->load('user');
            $user = $transaction->user;

            if ($user) {
                $valor = $transaction->amount;
                $taxa  = $user->taxa_cash_in ?? 0;
                $valorLiquido = intval(round($valor * (1 - ($taxa / 100))));

                $user->increment('saldo', $valorLiquido);

                Log::info('[PluggouWebhook] 💰 PIX aprovado via webhook', [
                    'user_id'         => $user->id,
                    'referenceCode'   => $referenceCode,
                    'valor_bruto'     => $valor,
                    'taxa'            => $taxa,
                    'valor_liquido'   => $valorLiquido,
                    'novo_saldo'      => $user->fresh()->saldo,
                ]);
            } else {
                Log::warning('[PluggouWebhook] Usuário não encontrado para transação', [
                    'referenceCode' => $referenceCode,
                ]);
            }
        } else {
            Log::info('[PluggouWebhook] Status não tratado: ' . $statusPluggou, [
                'referenceCode' => $referenceCode,
            ]);
        }
    }
}

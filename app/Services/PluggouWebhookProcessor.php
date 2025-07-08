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

        $externalId = $data['externalId'] ?? null;
        $statusPluggou = $data['status'] ?? null;

        if (!$externalId || !$statusPluggou) {
            Log::warning('[PluggouWebhook] Dados incompletos no payload', [
                'externalId' => $externalId,
                'status'     => $statusPluggou,
            ]);
            return;
        }

        $transaction = PixTransaction::where('reference_code', $externalId)->first();

        if (!$transaction) {
            Log::warning('[PluggouWebhook] Transação não encontrada', [
                'externalId' => $externalId,
            ]);
            return;
        }

        if ($transaction->status === 'paid') {
            Log::info('[PluggouWebhook] Transação já marcada como paga', [
                'reference_code' => $transaction->reference_code,
            ]);
            return;
        }

        if (!is_numeric($data['amount'] ?? null)) {
            Log::warning('[PluggouWebhook] Valor inválido para amount', [
                'externalId' => $externalId,
                'amount'     => $data['amount'] ?? null,
            ]);
            return;
        }

        if (strtoupper($statusPluggou) === 'APPROVED') {
            $valor = intval(round($data['amount']));

            $transaction->update([
                'status'          => 'paid',
                'amount'          => $valor,
                'customer_name'   => $data['customerName'] ?? null,
                'customer_email'  => $data['customerEmail'] ?? null,
                'paid_at'         => $data['paymentAt'] ?? now(),
                'description'     => $data['description'] ?? null,
                'raw_webhook'     => json_encode($payload), // opcional para auditoria
            ]);

            $user = $transaction->user;

            if ($user) {
                $taxa         = $user->taxa_cash_in ?? 0;
                $valorLiquido = intval(round($valor * (1 - ($taxa / 100))));

                $user->increment('saldo', $valorLiquido);

                Log::info('[PluggouWebhook] 💰 PIX aprovado via webhook', [
                    'user_id'       => $user->id,
                    'referenceCode' => $transaction->reference_code,
                    'valor_bruto'   => $valor,
                    'taxa'          => $taxa,
                    'valor_liquido' => $valorLiquido,
                    'novo_saldo'    => $user->fresh()->saldo,
                ]);
            } else {
                Log::warning('[PluggouWebhook] Usuário não encontrado para transação', [
                    'referenceCode' => $transaction->reference_code,
                ]);
            }
        } else {
            Log::info('[PluggouWebhook] Status não tratado: ' . $statusPluggou, [
                'externalId' => $externalId,
            ]);
        }
    }
}

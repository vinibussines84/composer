<?php

namespace App\Services;

use App\Models\PixTransaction;
use Illuminate\Support\Facades\Log;

class PluggouWebhookProcessor
{
    /**
     * Trata o payload do webhook da Pluggou.
     * Ajuste conforme a documentação oficial.
     */
    public function process(array $payload): void
    {
        // Exemplo: evento de mudança de status de pagamento
        if (($payload['event'] ?? null) === 'payment_status_changed') {

            $status         = $payload['data']['status']         ?? null;
            $referenceCode  = $payload['data']['referenceCode']  ?? null;

            if ($referenceCode && $status) {
                PixTransaction::where('reference_code', $referenceCode)
                    ->update(['status' => $status]);

                Log::info('[PluggouWebhook] Status atualizado', [
                    'referenceCode' => $referenceCode,
                    'newStatus'     => $status,
                ]);
            }
        }

        // → adicione outras regras conforme necessidade
    }
}

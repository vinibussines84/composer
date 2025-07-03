<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PluggouWebhook;
use App\Services\PluggouWebhookProcessor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PluggouWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // 1) Salva payload cru na DB
        $webhook = PluggouWebhook::create([
            'payload' => json_encode($payload),
            'status'  => 'pending',
        ]);

        // 2) Processa imediatamente se estiver habilitado em cache
        if (Cache::get('pluggou_auto_process', false)) {
            try {
                (new PluggouWebhookProcessor())->process($payload);

                $webhook->update(['status' => 'processed']);
            } catch (\Throwable $e) {
                $webhook->update(['status' => 'error']);
                Log::error('[PluggouWebhook] Falha ao processar', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 3) Resposta HTTP para a Pluggou
        return response()->json([
            'message' => 'Webhook recebido e ' .
                (Cache::get('pluggou_auto_process', false)
                    ? 'processado automaticamente.'
                    : 'aguardando aprovação.')
        ]);
    }
}

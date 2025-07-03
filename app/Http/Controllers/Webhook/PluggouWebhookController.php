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

        Log::info('[Webhook Pluggou] Payload recebido', [
            'body' => $payload,
        ]);

        // 1) Salva o payload no banco como array (Eloquent serializa automaticamente)
        $webhook = PluggouWebhook::create([
            'payload' => $payload,
            'status'  => 'pending',
        ]);

        Log::info('[Webhook Pluggou] Webhook salvo com ID ' . $webhook->id);

        // 2) Processa automaticamente se habilitado via cache
        if (Cache::get('pluggou_auto_process', false)) {
            try {
                (new PluggouWebhookProcessor())->process($payload);

                $webhook->update(['status' => 'processed']);
            } catch (\Throwable $e) {
                $webhook->update(['status' => 'error']);

                Log::error('[PluggouWebhook] Falha ao processar', [
                    'error'   => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                    'payload' => $payload,
                ]);
            }
        }

        // 3) Resposta HTTP para a Pluggou
        return response()->json([
            'message' => 'Webhook recebido e ' .
                (Cache::get('pluggou_auto_process', false)
                    ? 'processado automaticamente.'
                    : 'aguardando aprovação.'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BloobankWebhook;
use App\Services\BloobankWebhookProcessor;
use Illuminate\Support\Facades\Cache;

class BloobankWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $webhook = BloobankWebhook::create([
            'payload' => json_encode($payload),
            'status' => 'pending',
        ]);

        if (Cache::get('bloobank_auto_process', false)) {
            try {
                (new BloobankWebhookProcessor())->process($payload);

                $webhook->update(['status' => 'processed']);
            } catch (\Throwable $e) {
                $webhook->update(['status' => 'error']);
                // Logar erro ou notificar aqui se desejar
            }
        }

        return response()->json([
            'message' => 'Webhook recebido e ' . (Cache::get('bloobank_auto_process', false) ? 'processado automaticamente' : 'aguardando aprovação') . '.'
        ]);
    }
}

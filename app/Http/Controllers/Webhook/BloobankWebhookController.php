<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BloobankWebhook;

class BloobankWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        BloobankWebhook::create([
            'payload' => json_encode($payload),
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Webhook recebido e aguardando aprovação.']);
    }
}

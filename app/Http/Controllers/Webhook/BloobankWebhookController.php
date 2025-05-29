<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PixTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BloobankWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $data = $payload['body'] ?? null;

        if (! $data || ! isset($data['id'], $data['status'])) {
            return response()->json(['error' => 'Payload inválido'], 422);
        }

        $status = $data['status'];
        $bloobankId = $data['id'];

        $transaction = PixTransaction::where('external_transaction_id', $bloobankId)->first();

        if (! $transaction) {
            Log::warning("Webhook Bloobank: Transação não encontrada: $bloobankId");
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        $user = User::find($transaction->user_id);

        if (! $user) {
            Log::warning("Webhook Bloobank: Usuário não encontrado para transação $bloobankId");
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        DB::beginTransaction();

        try {
            $valor = $transaction->amount;

            if ($status === 'approved') {
                $taxa = (int) (($user->cashin_taxa / 100) * $valor);
                $liquido = $valor - $taxa;

                $user->saldo += $liquido;
                $user->save();

                $transaction->status = 'paid';
                $transaction->save();

                Log::info("✅ Pagamento confirmado: $bloobankId | +$liquido (Taxa: $taxa)");
            }

            if ($status === 'chargeback') {
                $user->saldo -= $valor;
                $user->save();

                $transaction->status = 'chargeback';
                $transaction->save();

                Log::warning("⚠️ Chargeback registrado: $bloobankId | -$valor");
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Erro no webhook Bloobank: " . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
}

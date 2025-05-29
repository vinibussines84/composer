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
            $valor = $transaction->amount; // em centavos
            $taxa = max(0, min(100, (float) ($user->taxa_cash_in ?? 0))); // defensivo

            if ($status === 'approved' && $transaction->status !== 'paid') {
                $desconto = (int) round($valor * ($taxa / 100));
                $valorLiquido = $valor - $desconto;

                $user->increment('saldo', $valorLiquido);

                $transaction->update([
                    'status' => 'paid',
                ]);

                Log::info("✅ Pagamento confirmado Bloobank: $bloobankId", [
                    'user_id' => $user->id,
                    'valor_bruto' => $valor,
                    'taxa' => $taxa,
                    'desconto' => $desconto,
                    'creditado' => $valorLiquido,
                ]);
            }

            if ($status === 'chargeback' && $transaction->status !== 'chargeback') {
                $user->decrement('saldo', $valor);
                $transaction->update([
                    'status' => 'chargeback',
                ]);

                Log::warning("⚠️ Chargeback Bloobank: $bloobankId", [
                    'user_id' => $user->id,
                    'valor_retirado' => $valor,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("❌ Erro no webhook Bloobank: " . $e->getMessage());
            return response()->json(['error' => 'Erro interno'], 500);
        }
    }
}

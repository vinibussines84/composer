<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PixTransaction;
use App\Services\BloobankService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PixController extends Controller
{
    public function handle(Request $request)
    {
        $authkey = $request->header('authkey');
        $gtkey   = $request->header('gtkey');

        if (! $authkey || ! $gtkey) {
            return response()->json(['error' => 'Credenciais ausentes'], 401);
        }

        $user = User::where('authkey', $authkey)
                    ->where('gtkey', $gtkey)
                    ->first();

        if (! $user) {
            return response()->json(['error' => 'Credenciais inválidas'], 403);
        }

        if ($user->cashin_ativo != 1) {
            return response()->json(['error' => 'CashIn desativado para este usuário'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $rawAmount = $validated['amount'];
        $amountInCents = is_float($rawAmount) || $rawAmount < 1000
            ? (int) ($rawAmount * 100)
            : (int) $rawAmount;

        try {
            $bloobank = new BloobankService();

            $response = $bloobank->createPixPayment($user->toArray(), $amountInCents);
            $data = $response['json'];

            $isValid = isset($data['id'], $data['pix']['copypaste']);

            if (! $isValid) {
                Log::error('Bloobank retornou erro: ', [
                    'status' => $response['status'],
                    'body' => $response['body'],
                ]);
                return response()->json([
                    'error' => 'Falha ao criar pagamento Bloobank',
                    'details' => $data,
                ], $response['status']);
            }

            PixTransaction::create([
                'user_id' => $user->id,
                'authkey' => $authkey,
                'gtkey' => $gtkey,
                'provedora' => 'Bloobank',
                'external_transaction_id' => $data['id'] ?? '',
                'txid' => $data['id'] ?? '',
                'amount' => $amountInCents,
                'status' => $data['status'] ?? 'unknown',
                'pix' => $data['pix'] ?? [],
                'created_at_api' => isset($data['createdAt'])
                    ? Carbon::parse($data['createdAt'])->utc()
                    : now()->utc(),
            ]);

            return response()->json([
                'id' => $data['id'],
                'status' => $data['status'],
                'amount' => number_format($amountInCents / 100, 2, '.', ''),
                'copypaste' => $data['pix']['copypaste'],
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento Pix Bloobank: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao processar: ' . $e->getMessage()], 500);
        }
    }
}
//k
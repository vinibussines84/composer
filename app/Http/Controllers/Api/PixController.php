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
        Log::info('Recebendo requisição Pix', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

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
                Log::error('Bloobank retornou erro inesperado', [
                    'status' => $response['status'],
                    'body' => $response['body'],
                ]);
                return response()->json([
                    'error' => 'Unauthorized for this request #000001',
                ], $response['status']);
            }

            PixTransaction::create([
                'user_id' => $user->id,
                'authkey' => $authkey,
                'gtkey' => $gtkey,
                'provedora' => 'Bloobank',
                'external_transaction_id' => $data['id'],
                'txid' => $data['id'],
                'amount' => $amountInCents,
                'status' => $data['status'],
                'pix' => $data['pix'],
                'created_at_api' => isset($data['createdAt'])
                    ? Carbon::parse($data['createdAt'])->utc()
                    : now()->utc(),
            ]);

            Log::info('Transação Pix salva com sucesso', [
                'user_id' => $user->id,
                'transaction_id' => $data['id'],
            ]);

            return response()->json([
                'id' => $data['id'],
                'status' => $data['status'],
                'amount' => number_format($amountInCents / 100, 2, '.', ''),
                'pix' => $data['pix'], // ✅ Retorna estrutura completa com `copypaste`
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento Pix Bloobank', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Erro ao processar: ' . $e->getMessage()], 500);
        }
    }

    public function status(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $transaction = PixTransaction::where('external_transaction_id', $request->input('id'))->first();

        if (! $transaction) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        return response()->json([
            'id' => $transaction->external_transaction_id,
            'status' => $transaction->status,
            'amount' => number_format($transaction->amount / 100, 2, '.', ''),
        ]);
    }
}
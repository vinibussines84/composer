<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawRequest;
use App\Services\BloobankService;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    public function handleWithdraw(Request $request)
    {
        $authkey = $request->header('authkey');
        $gtkey = $request->header('gtkey');

        if (!$authkey || !$gtkey) {
            return response()->json(['error' => 'Authkey e Gtkey são obrigatórios nos headers'], 400);
        }

        $user = User::where('authkey', $authkey)->where('gtkey', $gtkey)->first();

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado ou credenciais inválidas'], 401);
        }

        $request->validate([
            'pix_key' => 'required|string',
            'pix_type' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amountInCents = intval($request->input('amount') * 100);

        if ($user->saldo < $amountInCents) {
            return response()->json(['error' => 'Saldo insuficiente'], 400);
        }

        // Criar a requisição de saque pendente
        $withdrawRequest = WithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => $amountInCents,
            'pix_key' => $request->input('pix_key'),
            'pix_type' => $request->input('pix_type'),
            'status' => 'pending',
        ]);

        $bloobank = app(BloobankService::class);

        $payload = [
            'amount' => [
                'value' => $amountInCents,
            ],
            'pix' => [
                'key' => $withdrawRequest->pix_key,
                'type' => $withdrawRequest->pix_type,
                'name' => $user->name ?? 'TrustGateway',
            ],
            'description' => 'Saque via API',
            'externalReference' => 'saque-' . $withdrawRequest->id,
        ];

        $response = $bloobank->createPayout($payload);

        if ($response['ok']) {
            // Atualiza status e saldo
            $withdrawRequest->update(['status' => 'autorizado']);
            $user->decrement('saldo', $amountInCents);

            return response()->json([
                'message' => 'Saque autorizado com sucesso',
                'bloobank_response' => $response['json'],
            ]);
        } else {
            // Atualiza status para cancelado
            $withdrawRequest->update(['status' => 'cancelado']);

            return response()->json([
                'error' => 'Falha ao realizar saque',
                'details' => $response['json'],
            ], 500);
        }
    }
}

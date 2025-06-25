<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawRequest;
use App\Models\PixTransaction;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    public function handleWithdraw(Request $request)
    {
        $authkey = $request->header('authkey');
        $gtkey = $request->header('gtkey');

        if (!$authkey || !$gtkey) {
            return response()->json(['error' => 'Credenciais authkey e gtkey são obrigatórias'], 400);
        }

        $user = User::where('authkey', $authkey)
            ->where('gtkey', $gtkey)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        $pixKey = $request->input('pix_key');
        $pixType = $request->input('pix_type');
        $amount = $request->input('amount');

        if (!$pixKey || !$pixType || !$amount) {
            return response()->json(['error' => 'pix_key, pix_type e amount são obrigatórios'], 400);
        }

        // Converte valor para centavos (int)
        $valorEmCentavos = (int) round(floatval($amount) * 100);

        // Calcula taxa (percentual)
        $taxaPercentual = $user->taxa_cash_out ?? 0;
        $valorTaxa = (int) round($valorEmCentavos * ($taxaPercentual / 100));

        $valorTotal = $valorEmCentavos + $valorTaxa;

        // Verifica saldo disponível
        if ($valorTotal > ($user->saldo - $user->bloqueado)) {
            return response()->json(['error' => 'Saldo insuficiente'], 400);
        }

        // Debita saldo (valor + taxa)
        $user->decrement('saldo', $valorTotal);

        // Cria o saque
        $withdrawRequest = WithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => $valorEmCentavos,
            'pix_key' => $pixKey,
            'pix_type' => $pixType,
            'status' => 'pending',
        ]);

        // Cria registro de PixTransaction (opcional, ajusta conforme seu modelo)
        PixTransaction::create([
            'user_id' => $user->id,
            'authkey' => $user->authkey,
            'gtkey' => $user->gtkey,
            'external_transaction_id' => 'saque-' . $withdrawRequest->id,
            'amount' => -$valorEmCentavos, // somente o valor bruto, sem taxa
            'balance_type' => 0,
            'status' => $withdrawRequest->status,
            'created_at_api' => now(),
        ]);

        return response()->json([
            'success' => true,
            'withdraw_id' => $withdrawRequest->id,
            'amount' => $valorEmCentavos,
            'taxa' => $valorTaxa,
            'total_debitado' => $valorTotal,
            'status' => $withdrawRequest->status,
        ]);
    }
}

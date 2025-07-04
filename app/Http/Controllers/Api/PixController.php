<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PixTransaction;
use App\Services\PluggouService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PixController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Recebendo requisição Pix', [
            'headers' => $request->headers->all(),
            'body'    => $request->all(),
        ]);

        $authkey = $request->header('authkey');
        $gtkey   = $request->header('gtkey');

        if (!$authkey || !$gtkey) {
            return response()->json(['error' => 'Credenciais ausentes'], 401);
        }

        $user = User::where('authkey', $authkey)->where('gtkey', $gtkey)->first();
        if (!$user) {
            return response()->json(['error' => 'Credenciais inválidas'], 403);
        }

        if ($user->cashin_ativo != 1) {
            return response()->json(['error' => 'CashIn desativado para este usuário'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $rawAmount   = $validated['amount'];
        $amountCents = (int) round($rawAmount * 100); // para armazenar em centavos

        $payload = [
            'amount'               => $rawAmount, // Enviar para Pluggou em reais
            'customerName'         => 'Equatorial Brasil',
            'customerEmail'        => 'energiasbr@gmail.com',
            'customerPhone'        => $user->phone ?: '',
            'customerDocument'     => $user->document ?: '',
            'customerDocumentType' => $user->document_type ?: 'cpf',
            'description'          => 'Depósito via Pix - PlugouTrust',
            'metadata'             => [
                'user_id'    => $user->id,
                'referencia' => 'pix_' . now()->timestamp,
            ],
        ];

        $payload             = array_filter($payload, fn ($v) => $v !== '' && $v !== null);
        $payload['metadata'] = array_filter($payload['metadata']);

        try {
            $pluggou  = new PluggouService();
            $response = $pluggou->createPix($payload);
            $data     = $response['json'];

            if (
                !isset($data['pix']) ||
                !isset($data['pix']['qrCode']['emv']) ||
                !isset($data['status'])
            ) {
                Log::error('Resposta Pluggou inválida ou incompleta', ['data' => $data, 'body' => $response['body']]);
                return response()->json([
                    'error'   => 'Erro Pluggou',
                    'message' => 'Resposta incompleta da provedora',
                ], 502);
            }

            $referenceCode = $data['referenceCode'] ?? $data['id'] ?? null;
            $qrCode        = $data['pix']['qrCode']['emv'];
            $txid          = $data['pix']['txid'] ?? $data['id'];

            PixTransaction::create([
                'user_id'                 => $user->id,
                'authkey'                 => $authkey,
                'gtkey'                   => $gtkey,
                'provedora'               => 'Pluggou',
                'external_transaction_id' => $data['id'],
                'reference_code'          => $referenceCode,
                'txid'                    => $txid,
                'amount'                  => $amountCents,
                'status'                  => $data['status'],
                'pix'                     => $data['pix'],
                'created_at_api'          => Carbon::parse($data['paymentInfo']['createdAt'])->utc(),
            ]);

            return response()->json([
                'id'        => $referenceCode,
                'status'    => $data['status'],
                'amount'    => number_format($amountCents / 100, 2, '.', ''),
                'copypaste' => $qrCode,
                'txid'      => $txid,
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao criar Pix Pluggou', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno ao processar'], 500);
        }
    }

    public function status(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $transaction = PixTransaction::where('external_transaction_id', $request->input('id'))
                                     ->orWhere('reference_code', $request->input('id'))
                                     ->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        return response()->json([
            'id'     => $transaction->reference_code ?: $transaction->external_transaction_id,
            'status' => $transaction->status,
            'amount' => number_format($transaction->amount / 100, 2, '.', ''),
        ]);
    }
}

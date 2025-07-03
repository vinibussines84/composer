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
    /**
     * POST /api/pix
     * Headers  : authkey / gtkey
     * Body JSON: { "amount": <valor em reais> }
     */
    public function handle(Request $request)
    {
        Log::info('Recebendo requisição Pix', [
            'headers' => $request->headers->all(),
            'body'    => $request->all(),
        ]);

        /* -----------------------------------------------------------------
         | 1. Autenticação
         |-----------------------------------------------------------------*/
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

        /* -----------------------------------------------------------------
         | 2. Validação e conversão do amount
         |-----------------------------------------------------------------*/
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $rawAmount     = $validated['amount'];              // 93 ou 93.50
        $hasDecimals   = fmod($rawAmount, 1.0) !== 0.0;
        $amountPlg     = $hasDecimals ? (int) round($rawAmount * 100) : (int) $rawAmount;
        $amountCents   = (int) round($rawAmount * 100);     // sempre centavos p/ BD

        /* -----------------------------------------------------------------
         | 3. Monta payload automático
         |-----------------------------------------------------------------*/
        $payload = [
            'amount'               => $amountPlg,
            'customerName'         => $user->name                ?: 'Cliente TrustGateway',
            'customerEmail'        => $user->email               ?: 'no-reply@trustgateway.io',
            'customerPhone'        => $user->phone               ?: '',
            'customerDocument'     => $user->document            ?: '',
            'customerDocumentType' => $user->document_type       ?: 'cpf',
            'description'          => 'Depósito via Pix - ' . ($user->name ?: 'Cliente'),
            'metadata'             => [
                'user_id'    => $user->id,
                'referencia' => 'pix_' . now()->timestamp,
            ],
        ];

        $payload             = array_filter($payload, fn ($v) => $v !== '' && $v !== null);
        $payload['metadata'] = array_filter($payload['metadata']);

        /* -----------------------------------------------------------------
         | 4. Chamada à Pluggou
         |-----------------------------------------------------------------*/
        try {
            $pluggou  = new PluggouService();
            $response = $pluggou->createPix($payload);
            $data     = $response['json'];

            $referenceCode = $data['referenceCode']   ?? $data['id'];
            $qrCode        = $data['pix']['qrCode']['emv']   ?? null;
            $txid          = $data['pix']['txid']            ?? null;

            if (!$qrCode) {
                Log::error('Resposta Pluggou sem qrCode.emv', $data);
                return response()->json([
                    'error'   => 'Erro Pluggou',
                    'message' => $response['body'],
                ], 502);
            }

            /* -----------------------------------------------------------------
             | 5. Persistência local
             |-----------------------------------------------------------------*/
            PixTransaction::create([
                'user_id'                 => $user->id,
                'authkey'                 => $authkey,
                'gtkey'                   => $gtkey,
                'provedora'               => 'Pluggou',
                'external_transaction_id' => $data['id'],
                'reference_code'          => $referenceCode,
                'txid'                    => $txid ?: $data['id'],
                'amount'                  => $amountCents,
                'status'                  => $data['status'],
                'pix'                     => $data['pix'],
                'created_at_api'          => Carbon::parse($data['paymentInfo']['createdAt'])->utc(),
            ]);

            /* -----------------------------------------------------------------
             | 6. Resposta enxuta para o caller
             |     “id” mostra agora o referenceCode (tx_...)
             |-----------------------------------------------------------------*/
            return response()->json([
                'id'        => $referenceCode,                            // tx_1751548181905_683
                'status'    => $data['status'],
                'amount'    => number_format($amountCents / 100, 2, '.', ''),
                'copypaste' => $qrCode,
                'txid'      => $txid ?: $data['id'],
            ]);

        } catch (\Throwable $e) {
            Log::error('Erro ao criar Pix Pluggou', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno ao processar'], 500);
        }
    }

    /**
     * GET /api/pix/status?id=<external_id>
     */
    public function status(Request $request)
    {
        $request->validate(['id' => 'required|string']);

        $t = PixTransaction::where('external_transaction_id', $request->input('id'))
                           ->orWhere('reference_code', $request->input('id'))
                           ->first();

        if (!$t) {
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }

        return response()->json([
            'id'     => $t->reference_code ?: $t->external_transaction_id,
            'status' => $t->status,
            'amount' => number_format($t->amount / 100, 2, '.', ''),
        ]);
    }
}

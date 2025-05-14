<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PixTransaction;
use Illuminate\Support\Facades\Http;
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

        // Corrige valor para centavos, detectando se já foi enviado como centavos
        $rawAmount = $validated['amount'];
        $amountInCents = is_float($rawAmount) || $rawAmount < 1000
            ? (int) ($rawAmount * 100)
            : (int) $rawAmount;

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => 'Basic c2tfVV9paGVSNW53aUNVZjBGNFRMMXptUzluN011ZUtOT3V4WVgzWFc3aHFRNGNkNV91OnBrXy13dzU3T0RnOURyYTZFN3BzNHZuVzc2UFZtS1FQOXdCNkVYSFN0bXBhZldNUmtUaA==',
                'content-type' => 'application/json',
            ])->post('https://api.payonhub.com/v1/transactions', [
                'paymentMethod' => 'pix',
                'customer' => [
                    'document' => [
                        'type' => 'cpf',
                        'number' => '144.930.300-54'
                    ],
                    'name' => 'SPX',
                    'email' => 'SPX@GMAIL.COM'
                ],
                'items' => [[
                    'title' => 'SPX',
                    'unitPrice' => $amountInCents,
                    'quantity' => 1,
                    'tangible' => false
                ]],
                'amount' => $amountInCents,
            ]);

            $data = json_decode($response->body(), true);

            // Grava no banco com provedora fixa
            PixTransaction::create([
                'user_id' => $user->id,
                'authkey' => $authkey,
                'gtkey' => $gtkey,
                'provedora' => 'PayOnHub', // <- AQUI: provedora vinculada ao controlador
                'external_transaction_id' => $data['id'] ?? '',
                'amount' => $amountInCents,
                'status' => $data['status'] ?? 'unknown',
                'pix' => $data['pix'] ?? [],
                'created_at_api' => isset($data['createdAt'])
                    ? Carbon::parse($data['createdAt'])->utc()
                    : now()->utc(),
            ]);

            return response()->json([
                'id'        => $data['id'] ?? null,
                'amount'    => isset($data['amount']) ? number_format($data['amount'] / 100, 2, '.', '') : null,
                'status'    => $data['status'] ?? null,
                'createdAt' => $data['createdAt'] ?? null,
                'updatedAt' => $data['updatedAt'] ?? null,
                'pix'       => $data['pix'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro na requisição: ' . $e->getMessage()], 500);
        }
    }
}

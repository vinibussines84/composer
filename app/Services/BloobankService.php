<?php

namespace App\Services;

use App\Helpers\BloobankAuth;
use Illuminate\Support\Facades\Http;

class BloobankService
{
    protected string $accessKey;
    protected string $privateKey;

    public function __construct()
    {
        $this->accessKey  = 'BZxvFBwBUftDm1Kf9RPDGAv598WseiyyLZZpv46J2BTA';
        $this->privateKey = file_get_contents(storage_path('bloobank/privateKey.pem'));
    }

    public function createPixPayment(array $user, int $amountInCents): array
    {
        // 📞 Formata o telefone para +55XXXXXXXXXXX
        $rawPhone = $user['phone'] ?? '11999999999';
        $digitsOnly = preg_replace('/\D/', '', $rawPhone);
        $formattedPhone = '+55' . $digitsOnly;

        $payload = [
            'method' => 'pix',
            'amount' => [
                'value' => $amountInCents
            ],
            'customer' => [
                'doc' => [
                    'type' => 'CPF',
                    'value' => '14493030054',
                ],
                'name' => $user['name'] ?? 'Usuário Pix',
                'phone' => $formattedPhone,
                'email' => $user['email'] ?? 'sem-email@exemplo.com',
            ],
            'pix' => [
                'expiresIn' => 600
            ],
            'installments' => 1,
            'metadata' => [
                'user_id' => $user['id'] ?? null,
                'via' => 'api_pix_tb3'
            ],
        ];

        $auth = BloobankAuth::generateSignature($this->accessKey, $this->privateKey, $payload);

        $response = Http::withHeaders([
            'X-Access-Key' => $this->accessKey,
            'X-Access-Timestamp' => $auth['timestamp'],
            'X-Access-Signature' => $auth['signature'],
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://payment.blooapis.io/v1/payments', $payload);

        return [
            'status' => $response->status(),
            'ok' => $response->ok(),
            'body' => $response->body(),
            'json' => $response->json(),
            'headers' => $response->headers(),
        ];
    }
}

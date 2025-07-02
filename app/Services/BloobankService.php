<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BloobankService
{
    protected string $pluggouApiKey;
    protected string $pluggouOrganizationId;

    public function __construct()
    {
        $this->pluggouApiKey = config('services.pluggou.api_key');
        $this->pluggouOrganizationId = config('services.pluggou.organization_id');
    }

    /**
     * Cria um depósito Pix via Pluggou
     */
    public function createPluggouDeposit(array $data): array
    {
        $payload = [
            'amount' => $data['amount'], // obrigatório
            'customerName' => $data['customerName'], // obrigatório
            'customerEmail' => $data['customerEmail'], // obrigatório
            'organizationId' => $this->pluggouOrganizationId, // obrigatório
        ];

        // Campos opcionais
        if (!empty($data['customerPhone'])) {
            $payload['customerPhone'] = $data['customerPhone'];
        }

        if (!empty($data['customerDocument'])) {
            $payload['customerDocument'] = $data['customerDocument'];
        }

        if (!empty($data['customerDocumentType'])) {
            $payload['customerDocumentType'] = $data['customerDocumentType']; // cpf ou cnpj
        }

        if (!empty($data['description'])) {
            $payload['description'] = $data['description'];
        }

        if (!empty($data['metadata']) && is_array($data['metadata'])) {
            $payload['metadata'] = $data['metadata'];
        }

        $response = Http::withHeaders([
            'X-API-Key' => $this->pluggouApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://app.pluggou.io/api/payments/transactions', $payload);

        return [
            'status' => $response->status(),
            'ok' => $response->ok(),
            'body' => $response->body(),
            'json' => $response->json(),
        ];
    }
}
//
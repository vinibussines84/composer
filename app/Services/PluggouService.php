<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PluggouService
{
    /** @var string Chave de API (header X-API-Key) */
    private string $apiKey;

    /** @var string ID da organização */
    private string $organizationId;

    /** @var string URL base da API (produção) */
    private string $baseUrl = 'https://app.pluggou.io/api/payments';

    public function __construct()
    {
        $this->apiKey         = config('services.pluggou.api_key');
        $this->organizationId = config('services.pluggou.organization_id');
    }

    /* -----------------------------------------------------------------
     |  Métodos públicos
     |-----------------------------------------------------------------*/

    /**
     * Cria uma transação PIX.
     *
     * @param  array  $data  Campos obrigatórios: amount (centavos), customerName, customerEmail
     * @return array [status, ok, json, body]
     */
    public function createPix(array $data): array
    {
        $payload = array_merge($data, [
            'organizationId' => $this->organizationId,
        ]);

        $response = $this->client()->post("{$this->baseUrl}/transactions", $payload);

        return $this->wrap($response);
    }

    /**
     * Lista transações PIX (paginável).
     *
     * @param  array  $query  Ex.: ['page' => 2, 'perPage' => 50]
     * @return array
     */
    public function listPix(array $query = []): array
    {
        $query = array_merge($query, [
            'organizationId' => $this->organizationId,
        ]);

        $response = $this->client()->get("{$this->baseUrl}/transactions", $query);

        return $this->wrap($response);
    }

    /**
     * Busca transação PIX por ID.
     *
     * @param  string  $id
     * @return array
     */
    public function fetchPix(string $id): array
    {
        $response = $this->client()->get(
            "{$this->baseUrl}/transactions/{$id}",
            ['organizationId' => $this->organizationId]
        );

        return $this->wrap($response);
    }

    /* -----------------------------------------------------------------
     |  Helpers privados
     |-----------------------------------------------------------------*/

    /** Cria o client HTTP com cabeçalhos fixos. */
    private function client()
    {
        return Http::withHeaders([
            'X-API-Key'    => $this->apiKey,
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Empacota a resposta num array simples.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return array
     */
    private function wrap($response): array
    {
        return [
            'status' => $response->status(),
            'ok'     => $response->ok(),
            'json'   => $response->json(),
            'body'   => $response->body(),
        ];
    }
}

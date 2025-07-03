<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    /**
     * Cria uma transação PIX.
     *
     * @param  array  $data
     * @return array
     */
    public function createPix(array $data): array
    {
        $payload = array_merge($data, [
            'organizationId' => $this->organizationId,
        ]);

        $start = microtime(true);

        $response = $this->client()->post("{$this->baseUrl}/transactions", $payload);

        $duration = microtime(true) - $start;
        Log::info('⏱️ Pluggou - Tempo de resposta createPix', [
            'tempo_em_segundos' => round($duration, 3),
            'status_http' => $response->status(),
        ]);

        return $this->wrap($response);
    }

    /**
     * Lista transações PIX (paginável).
     *
     * @param  array  $query
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

    /** Cria o client HTTP com cabeçalhos fixos e timeout curto. */
    private function client()
    {
        return Http::timeout(3)
            ->withHeaders([
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

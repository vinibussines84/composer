<?php

namespace App\Helpers;

class BloobankAuth
{
    public static function generateSignature(string $accessKey, string $privateKeyPem, array $body = []): array
    {
        $timestamp = (int) (microtime(true) * 1000);
        $jsonBody = $body ? json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $message = $accessKey . '|' . $jsonBody . '|' . $timestamp;

        $privateKeyResource = openssl_pkey_get_private($privateKeyPem);
        if (!$privateKeyResource) {
            throw new \Exception('Chave privada inválida');
        }

        if (!openssl_sign($message, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
            throw new \Exception('Erro ao assinar');
        }

        return [
            'timestamp' => $timestamp,
            'signature' => base64_encode($signature),
        ];
    }
}

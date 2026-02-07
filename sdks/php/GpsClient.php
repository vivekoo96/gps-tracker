<?php

namespace GpsTracker\Sdk;

class GpsClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct(string $baseUrl, string $apiKey, string $apiSecret)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * Get all devices.
     */
    public function getDevices(array $params = []): array
    {
        return $this->request('GET', '/devices', $params);
    }

    /**
     * Get tracking history for a device.
     */
    public function getHistory(string $deviceId, array $params = []): array
    {
        return $this->request('GET', "/gps-data/{$deviceId}/history", $params);
    }

    /**
     * Post new GPS data.
     */
    public function postGpsData(array $data): array
    {
        return $this->request('POST', '/gps-data', [], $data);
    }

    /**
     * Internal request handler.
     */
    protected function request(string $method, string $endpoint, array $queryParams = [], array $body = []): array
    {
        $url = $this->baseUrl . '/api/v2' . $endpoint;
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-API-Key: ' . $this->apiKey,
            'X-API-Secret: ' . $this->apiSecret,
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            throw new \Exception($data['error']['message'] ?? 'API Request Failed', $httpCode);
        }

        return $data;
    }

    /**
     * Verify Webhook Signature.
     */
    public function verifyWebhook(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->apiSecret);
        return hash_equals($expectedSignature, $signature);
    }
}

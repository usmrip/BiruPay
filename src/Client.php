<?php
namespace BiruPay;

use BiruPay\Exception\BiruPayException;

class Client {
    public const DEFAULT_BASE_URL = 'https://biru.co.id/pembayaran';
    public const VERSION = '1.0.0';

    private string $apiKey;
    private string $baseUrl;
    private int $timeout;

    public function __construct(string $apiKey, string $baseUrl = self::DEFAULT_BASE_URL, int $timeout = 15) {
        $this->apiKey  = trim($apiKey);
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;

        if (empty($this->apiKey)) {
            throw new BiruPayException('API Key tidak boleh kosong.');
        }
    }

    /**
     * Buat invoice pembayaran baru di Biru PayHub
     */
    public function createPayment(array $params): array {
        if (empty($params['order_id'])) {
            throw new BiruPayException('Parameter order_id wajib diisi.');
        }
        if (empty($params['amount']) || (float)$params['amount'] <= 0) {
            throw new BiruPayException('Parameter amount harus lebih dari 0.');
        }

        $endpoint = $this->baseUrl . '/api/v1/payment/create';
        $res = $this->request('POST', $endpoint, $params);

        if (($res['status'] ?? '') !== 'success') {
            throw new BiruPayException($res['message'] ?? 'Gagal membuat tagihan pembayaran.', 0, $res);
        }

        return $res['data'] ?? [];
    }

    /**
     * Cek status pembayaran secara real-time
     */
    public function checkStatus(string $orderId): array {
        $endpoint = $this->baseUrl . '/api/v1/payment/status/' . urlencode($orderId);
        $res = $this->request('GET', $endpoint);

        if (($res['status'] ?? '') !== 'success') {
            throw new BiruPayException($res['message'] ?? 'Gagal mengambil status pembayaran.', 0, $res);
        }

        return $res['data'] ?? [];
    }

    /**
     * Verifikasi webhook callback masuk dari PayHub
     */
    public function verifyWebhook(?string $rawPayload = null, ?string $signatureHeader = null): Webhook {
        return Webhook::verify($this->apiKey, $rawPayload, $signatureHeader);
    }

    /**
     * Dapatkan tag script frontend birupay.js
     */
    public function getScriptTag(): string {
        return '<script src="' . htmlspecialchars($this->baseUrl) . '/birupay.js"></script>';
    }

    /**
     * Internal cURL Request
     */
    private function request(string $method, string $url, array $payload = []): array {
        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'X-Biru-ApiKey: ' . $this->apiKey,
            'User-Agent: BiruPay-Composer/' . self::VERSION
        ];

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            throw new BiruPayException('Koneksi ke Biru PayHub gagal: ' . $curlErr, $httpCode ?: 500);
        }

        $json = json_decode($response, true);
        if ($json !== null) {
            return $json;
        }

        throw new BiruPayException('Respons tidak terduga dari server (HTTP ' . $httpCode . '): ' . substr($response, 0, 200), $httpCode);
    }
}


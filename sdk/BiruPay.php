<?php
/**
 * BiruPay PHP SDK
 * Multi-Platform Centralized Payment Gateway
 * Biru Indonesia Creative (biru.co.id)
 * Version: 1.0.0
 * 
 * Penggunaan Sederhana:
 * 
 * require_once 'BiruPay.php';
 * $pay = new BiruPay('YOUR_API_KEY');
 * 
 * // Buat Pembayaran:
 * $res = $pay->createPayment([
 *     'order_id'       => 'INV-12345',
 *     'amount'         => 50000,
 *     'customer_name'  => 'Budi Santoso',
 *     'customer_email' => 'budi@gmail.com',
 *     'customer_phone' => '081234567890',
 *     'item_name'      => 'Pendaftaran Formulir VIP'
 * ]);
 */

if (!class_exists('BiruPay')) {
    class BiruPay {
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;

    public function __construct(string $apiKey, string $baseUrl = 'https://biru.co.id/pembayaran', int $timeout = 15) {
        $this->apiKey  = trim($apiKey);
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * Buat invoice pembayaran dan peroleh token popup / URL checkout
     */
    public function createPayment(array $params): array {
        $endpoint = $this->baseUrl . '/api/v1/payment/create';
        return $this->request('POST', $endpoint, $params);
    }

    /**
     * Cek status pembayaran secara real-time berdasarkan Order ID atau Referensi Hub
     */
    public function checkStatus(string $orderId): array {
        $endpoint = $this->baseUrl . '/api/v1/payment/status/' . urlencode($orderId);
        return $this->request('GET', $endpoint);
    }

    /**
     * Cek cepat apakah order sudah lunas (true jika status == success)
     */
    public function isPaid(string $orderId): bool {
        $res = $this->checkStatus($orderId);
        return isset($res['data']['status']) && $res['data']['status'] === 'success';
    }

    /**
     * Verifikasi webhook notifikasi pembayaran dari Biru PayHub
     */
    public function verifyWebhook(?string $rawPayload = null, ?string $signature = null): array {
        $raw = $rawPayload ?? file_get_contents('php://input');
        $sig = $signature ?? ($_SERVER['HTTP_X_BIRU_SIGNATURE'] ?? '');

        if (empty($raw)) {
            return [
                'is_valid' => false,
                'status'   => 'invalid',
                'message'  => 'Payload webhook kosong.'
            ];
        }

        $expectedSig = hash_hmac('sha256', $raw, $this->apiKey);
        $isValid = hash_equals($expectedSig, $sig);

        $data = json_decode($raw, true) ?: [];
        $event = $data['event'] ?? '';
        $trxData = $data['data'] ?? [];

        return [
            'is_valid'      => $isValid,
            'event'         => $event,
            'order_id'      => $trxData['order_id'] ?? '',
            'trx_reference' => $trxData['trx_reference'] ?? '',
            'amount'        => (float) ($trxData['amount'] ?? 0),
            'status'        => $trxData['status'] ?? ($event === 'payment.success' ? 'success' : 'pending'),
            'gateway'       => $trxData['gateway'] ?? '',
            'payment_method'=> $trxData['payment_method'] ?? '',
            'paid_at'       => $trxData['paid_at'] ?? null,
            'data'          => $trxData,
            'raw'           => $data
        ];
    }

    /**
     * Render skrip tag frontend birupay.js
     */
    public function renderScript(): string {
        return '<script src="' . htmlspecialchars($this->baseUrl) . '/birupay.js"></script>';
    }

    /**
     * Request HTTP helper
     */
    private function request(string $method, string $url, array $payload = []): array {
        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'X-Biru-ApiKey: ' . $this->apiKey,
            'User-Agent: BiruPay-PHP-SDK/1.0.0'
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
            return [
                'status'  => 'error',
                'message' => 'Koneksi ke Biru PayHub gagal: ' . $curlErr
            ];
        }

        $json = json_decode($response, true);
        if ($json !== null) {
            return $json;
        }

        return [
            'status'    => 'error',
            'message'   => 'Respons tidak terduga dari server (HTTP ' . $httpCode . ')',
            'raw_body'  => $response
        ];
    }
}
}



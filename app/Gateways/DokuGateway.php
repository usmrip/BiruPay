<?php
namespace App\Gateways;

use App\Core\Request;
use App\Models\GatewayConfig;
use App\Models\Log;
use App\Core\View;

class DokuGateway implements PaymentGatewayInterface {
    private ?array $config;

    public function __construct(?array $config = null) {
        $this->config = $config ?: GatewayConfig::getByCode('doku');
    }

    public function isConfigured(): bool {
        return !empty($this->config['client_key']) && !empty($this->config['server_key']);
    }

    private function isProduction(): bool {
        return !empty($this->config['is_production']);
    }

    private function getBaseUrl(): string {
        return $this->isProduction()
            ? 'https://api.doku.com'
            : 'https://api-sandbox.doku.com';
    }

    public function getFrontendConfig(): array {
        return [
            'gateway' => 'doku',
            'client_key' => $this->config['client_key'] ?? '',
            'is_production' => $this->isProduction(),
        ];
    }

    public function createPayment(array $params): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Konfigurasi DOKU belum lengkap (Client ID atau Secret Key belum diisi).'
            ];
        }

        $clientId  = trim($this->config['client_key']);
        $secretKey = trim($this->config['server_key']);
        $amount    = (int) round($params['amount']);
        $orderId   = $params['trx_reference'];
        $path      = '/checkout/v1/payment';

        $payload = [
            'order' => [
                'amount' => $amount,
                'invoice_number' => $orderId,
                'currency' => 'IDR',
                'callback_url' => View::url('payment/return?ref=' . urlencode($orderId)),
                'callback_url_cancel' => View::url('payment/cancel?ref=' . urlencode($orderId)),
                'line_items' => [
                    [
                        'name' => substr($params['item_name'] ?? 'Pembayaran Layanan Biru', 0, 50),
                        'price' => $amount,
                        'quantity' => 1
                    ]
                ]
            ],
            'payment' => [
                'payment_due_date' => 1440 // 24 hours
            ],
            'customer' => [
                'name' => $params['customer_name'] ?? 'Pelanggan Biru',
                'email' => $params['customer_email'] ?? 'customer@biru.co.id',
                'phone' => $params['customer_phone'] ?? '08123456789',
                'address' => 'Indonesia',
                'country' => 'ID'
            ],
            'additional_info' => [
                'app_code' => $params['app_code'] ?? 'BIRU',
                'app_id' => $params['app_id'] ?? '',
                'order_id' => $params['order_id'] ?? ''
            ]
        ];

        $jsonPayload = json_encode($payload);
        $digest = base64_encode(hash('sha256', $jsonPayload, true));
        $requestId = bin2hex(random_bytes(16));
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        $signatureComponent = "Client-Id:" . $clientId . "\n"
                            . "Request-Id:" . $requestId . "\n"
                            . "Request-Timestamp:" . $timestamp . "\n"
                            . "Request-Target:" . $path . "\n"
                            . "Digest:" . $digest;

        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $signatureComponent, $secretKey, true));

        $ch = curl_init($this->getBaseUrl() . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Client-Id: ' . $clientId,
            'Request-Id: ' . $requestId,
            'Request-Timestamp: ' . $timestamp,
            'Signature: ' . $signature
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error('doku', 'DOKU cURL error', ['error' => $curlError, 'payload' => $payload]);
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server DOKU: ' . $curlError
            ];
        }

        $result = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && !empty($result['response']['payment']['url'])) {
            return [
                'success' => true,
                'gateway' => 'doku',
                'reference' => $orderId,
                'trx_reference' => $orderId,
                'payment_url' => $result['response']['payment']['url'],
                'mode' => $this->isProduction() ? 'production' : 'sandbox'
            ];
        }

        $errorMessage = $result['error']['message'] ?? $result['message'] ?? 'Gagal membuat checkout DOKU (HTTP ' . $httpCode . ')';
        Log::error('doku', 'DOKU create checkout failed', ['http_code' => $httpCode, 'response' => $result, 'payload' => $payload]);

        return [
            'success' => false,
            'message' => 'DOKU Error: ' . $errorMessage
        ];
    }

    public function verifyWebhook(Request $request): array {
        $body = $request->json() ?? [];
        $raw = $request->rawBody();

        $secretKey = trim($this->config['server_key'] ?? '');
        $clientId  = trim($this->config['client_key'] ?? '');

        $orderId = $body['order']['invoice_number'] ?? '';
        $amount = (float)($body['order']['amount'] ?? 0);
        $trxStatus = strtoupper($body['transaction']['status'] ?? '');
        $paymentType = $body['payment']['payment_method_type'] ?? 'doku_checkout';

        if (empty($orderId)) {
            return [
                'is_valid' => false,
                'message' => 'Invoice number tidak ditemukan pada payload DOKU.'
            ];
        }

        // Validate HMAC signature if provided in header
        $headerSignature = $request->header('Signature');
        $requestId = $request->header('Request-Id');
        $timestamp = $request->header('Request-Timestamp');

        if ($headerSignature && $requestId && $timestamp) {
            $digest = base64_encode(hash('sha256', $raw, true));
            $path = '/webhook/doku';
            $signatureComponent = "Client-Id:" . $clientId . "\n"
                                . "Request-Id:" . $requestId . "\n"
                                . "Request-Timestamp:" . $timestamp . "\n"
                                . "Request-Target:" . $path . "\n"
                                . "Digest:" . $digest;
            $calculatedSignature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $signatureComponent, $secretKey, true));

            if (!hash_equals($calculatedSignature, $headerSignature)) {
                Log::warning('doku', 'DOKU signature mismatch', [
                    'received' => $headerSignature,
                    'calculated' => $calculatedSignature,
                    'order_id' => $orderId
                ]);
            }
        }

        $status = 'pending';
        if (in_array($trxStatus, ['SUCCESS', 'PAID', 'SETTLED'])) {
            $status = 'success';
        } elseif (in_array($trxStatus, ['FAILED', 'DENIED', 'CANCELLED'])) {
            $status = 'failed';
        } elseif ($trxStatus === 'EXPIRED') {
            $status = 'expired';
        }

        return [
            'is_valid' => true,
            'trx_reference' => $orderId,
            'gateway_reference' => $body['transaction']['id'] ?? $orderId,
            'status' => $status,
            'payment_method' => $paymentType,
            'amount' => $amount,
            'raw_payload' => $raw,
            'original_status' => $trxStatus,
            'message' => 'Verifikasi webhook DOKU sukses.'
        ];
    }
}


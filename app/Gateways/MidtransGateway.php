<?php
namespace App\Gateways;

use App\Core\Request;
use App\Models\GatewayConfig;
use App\Models\Log;

class MidtransGateway implements PaymentGatewayInterface {
    private ?array $config;

    public function __construct(?array $config = null) {
        $this->config = $config ?: GatewayConfig::getByCode('midtrans');
    }

    public function isConfigured(): bool {
        return !empty($this->config['server_key']) && !empty($this->config['client_key']);
    }

    private function isProduction(): bool {
        return !empty($this->config['is_production']);
    }

    private function getApiUrl(): string {
        return $this->isProduction()
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function getFrontendConfig(): array {
        return [
            'gateway' => 'midtrans',
            'client_key' => $this->config['client_key'] ?? '',
            'script_url' => $this->isProduction()
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
            'is_production' => $this->isProduction(),
        ];
    }

    public function createPayment(array $params): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Konfigurasi Midtrans belum lengkap (Server Key atau Client Key belum diisi).'
            ];
        }

        $grossAmount = (int) round($params['amount']);
        $payload = [
            'transaction_details' => [
                'order_id' => $params['trx_reference'],
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $params['customer_name'] ?? 'Pelanggan Biru',
                'email' => $params['customer_email'] ?? 'customer@biru.co.id',
                'phone' => $params['customer_phone'] ?? '08123456789',
            ],
            'custom_field1' => $params['app_code'] ?? 'BIRU',
            'custom_field2' => (string)($params['app_id'] ?? ''),
            'custom_field3' => $params['order_id'] ?? '',
        ];

        if (!empty($params['item_details']) && is_array($params['item_details'])) {
            $payload['item_details'] = $params['item_details'];
        } else {
            $payload['item_details'] = [[
                'id' => 'ITEM-1',
                'price' => $grossAmount,
                'quantity' => 1,
                'name' => substr($params['item_name'] ?? 'Pembayaran Layanan Biru', 0, 50)
            ]];
        }

        $serverKey = trim($this->config['server_key']);
        $authHeader = base64_encode($serverKey . ':');
        $webhookUrl = \App\Core\View::url('webhook/midtrans');

        $ch = curl_init($this->getApiUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . $authHeader,
            'X-Override-Notification: ' . $webhookUrl,
            'X-Append-Notification: ' . $webhookUrl,
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error('midtrans', 'Midtrans cURL error', ['error' => $curlError, 'payload' => $payload]);
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Midtrans: ' . $curlError
            ];
        }

        $result = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && !empty($result['token'])) {
            return [
                'success' => true,
                'gateway' => 'midtrans',
                'snap_token' => $result['token'],
                'payment_url' => $result['redirect_url'] ?? '',
                'reference' => $params['trx_reference'],
                'client_key' => $this->config['client_key'],
                'mode' => $this->isProduction() ? 'production' : 'sandbox',
                'script_url' => $this->getFrontendConfig()['script_url']
            ];
        }

        $errorMessage = $result['error_messages'][0] ?? $result['message'] ?? 'Respons tidak terduga dari Midtrans (HTTP ' . $httpCode . ')';
        Log::error('midtrans', 'Midtrans payment creation failed', ['http_code' => $httpCode, 'response' => $result, 'payload' => $payload]);

        return [
            'success' => false,
            'message' => 'Midtrans Error: ' . $errorMessage
        ];
    }

    public function verifyWebhook(Request $request): array {
        $body = $request->json() ?? [];
        $raw = $request->rawBody();

        if (empty($body)) {
            return [
                'is_valid' => false,
                'message' => 'Payload webhook kosong atau format bukan JSON.'
            ];
        }

        $orderId = $body['order_id'] ?? '';
        $statusCode = $body['status_code'] ?? '';
        $grossAmount = $body['gross_amount'] ?? '';
        $signatureKey = $body['signature_key'] ?? '';

        $serverKey = trim($this->config['server_key'] ?? '');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('midtrans', 'Midtrans invalid signature', [
                'expected' => $expectedSignature,
                'received' => $signatureKey,
                'order_id' => $orderId
            ]);
            return [
                'is_valid' => false,
                'message' => 'Signature Midtrans tidak valid.'
            ];
        }

        $trxStatus = $body['transaction_status'] ?? '';
        $fraudStatus = $body['fraud_status'] ?? '';
        $paymentType = $body['payment_type'] ?? '';

        $status = 'pending';
        if ($trxStatus === 'capture') {
            $status = ($fraudStatus === 'accept') ? 'success' : 'pending';
        } elseif ($trxStatus === 'settlement') {
            $status = 'success';
        } elseif (in_array($trxStatus, ['cancel', 'deny'])) {
            $status = 'failed';
        } elseif ($trxStatus === 'expire') {
            $status = 'expired';
        } elseif ($trxStatus === 'pending') {
            $status = 'pending';
        }

        return [
            'is_valid' => true,
            'trx_reference' => $orderId,
            'gateway_reference' => $body['transaction_id'] ?? '',
            'status' => $status,
            'payment_method' => $paymentType,
            'amount' => (float) $grossAmount,
            'raw_payload' => $raw,
            'original_status' => $trxStatus,
            'message' => 'Verifikasi webhook Midtrans sukses.'
        ];
    }
}

